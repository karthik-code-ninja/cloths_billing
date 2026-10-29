<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Order;
use App\Models\Printer;
use App\Models\PrintJob;
use App\Models\ProductVariant;
use App\Models\ReturnOrder;
use Exception;
use Illuminate\Support\Facades\Log;

class PrintService
{
    public function __construct(
        protected EscPosService $escpos
    ) {}

    /**
     * Dispatch an order receipt print job to the appropriate printer.
     */
    public function printOrderReceipt(Order $order, ?Printer $printer = null, bool $isReprint = false, string $type = 'standard'): PrintJob
    {
        $targetPrinter = $printer ?: $this->resolveDefaultPrinter($order->branch_id, 'receipt');

        $jobType = match ($targetPrinter->paper_width_mm) {
            58 => 'receipt_58',
            default => 'receipt_80',
        };

        if ($type === 'gift') {
            $jobType = 'gift_receipt';
        }

        $payload = $this->buildReceiptEscPosPayload($order, $targetPrinter, $isReprint, $type);

        $printJob = PrintJob::create([
            'printer_id' => $targetPrinter->id,
            'order_id' => $order->id,
            'job_type' => $jobType,
            'payload_version' => 'v1',
            'payload' => base64_encode($payload),
            'status' => 'pending',
            'attempts' => 0,
            'is_reprint' => $isReprint,
        ]);

        $this->executePrintJob($printJob, $targetPrinter, $payload);

        return $printJob;
    }

    /**
     * Dispatch a return receipt print job.
     */
    public function printReturnReceipt(ReturnOrder $returnOrder, ?Printer $printer = null, bool $isReprint = false): PrintJob
    {
        $targetPrinter = $printer ?: $this->resolveDefaultPrinter($returnOrder->branch_id, 'receipt');
        $payload = $this->buildReturnReceiptEscPosPayload($returnOrder, $targetPrinter, $isReprint);

        $printJob = PrintJob::create([
            'printer_id' => $targetPrinter->id,
            'order_id' => $returnOrder->original_order_id,
            'job_type' => 'return_receipt',
            'payload_version' => 'v1',
            'payload' => base64_encode($payload),
            'status' => 'pending',
            'attempts' => 0,
            'is_reprint' => $isReprint,
        ]);

        $this->executePrintJob($printJob, $targetPrinter, $payload);

        return $printJob;
    }

    /**
     * Dispatch a barcode label print job for a variant.
     */
    public function printBarcodeLabel(ProductVariant $variant, int $copies = 1, ?Printer $printer = null): PrintJob
    {
        $branchId = (int) (auth()->user()->branch_id ?? 1);
        $targetPrinter = $printer ?: $this->resolveDefaultPrinter($branchId, 'label');
        $payload = $this->buildBarcodeLabelPayload($variant, $copies, $targetPrinter);

        $printJob = PrintJob::create([
            'printer_id' => $targetPrinter->id,
            'order_id' => null,
            'job_type' => 'barcode_label',
            'payload_version' => 'v1',
            'payload' => base64_encode($payload),
            'status' => 'pending',
            'attempts' => 0,
            'is_reprint' => false,
        ]);

        $this->executePrintJob($printJob, $targetPrinter, $payload);

        return $printJob;
    }

    /**
     * Dispatch a batch barcode label print job for a queue supporting 1-Up or 2-Up rolls.
     *
     * @param  array<int, int>  $queue  Array of [variant_id => copies]
     */
    public function printBarcodeQueue(array $queue, ?Printer $printer = null, int $columns = 2, int $widthMm = 50, int $heightMm = 25): PrintJob
    {
        $branchId = (int) (auth()->user()->branch_id ?? 1);
        $targetPrinter = $printer ?: $this->resolveDefaultPrinter($branchId, 'label');
        $payload = $this->buildBarcodeQueuePayload($queue, $targetPrinter, $columns, $widthMm, $heightMm);

        $printJob = PrintJob::create([
            'printer_id' => $targetPrinter->id,
            'order_id' => null,
            'job_type' => 'barcode_label',
            'payload_version' => 'v1',
            'payload' => base64_encode($payload),
            'status' => 'pending',
            'attempts' => 0,
            'is_reprint' => false,
        ]);

        $this->executePrintJob($printJob, $targetPrinter, $payload);

        return $printJob;
    }

    /**
     * Execute test print diagnostic.
     */
    public function testPrint(Printer $printer): PrintJob
    {
        $cols = $printer->columns ?: 42;
        $buf = EscPosService::INIT;
        $buf .= EscPosService::ALIGN_CENTER;
        $buf .= EscPosService::TEXT_BOLD_ON."NEXTDNA PRINTER DIAGNOSTICS\n".EscPosService::TEXT_BOLD_OFF;
        $buf .= "Printer: {$printer->name}\n";
        $buf .= "Connection: {$printer->connection_type}\n";
        $buf .= "Width: {$printer->paper_width_mm}mm ({$cols} cols)\n";
        $buf .= $this->escpos->divider('=', $cols);
        $buf .= EscPosService::ALIGN_LEFT;
        $buf .= 'Timestamp: '.now()->format('d/m/Y h:i A')."\n";
        $buf .= "DPI: {$printer->dpi} | Cut: ".($printer->has_cutter ? 'Yes' : 'No')."\n";
        $buf .= $this->escpos->divider('-', $cols);
        $buf .= EscPosService::ALIGN_CENTER;
        $buf .= "Thermal Print Test Successful!\n\n";

        if ($printer->has_cutter) {
            $buf .= EscPosService::CUT_FEED_AND_CUT;
        } else {
            $buf .= str_repeat("\n", $printer->feed_lines ?: 3);
        }

        $printJob = PrintJob::create([
            'printer_id' => $printer->id,
            'order_id' => null,
            'job_type' => 'test_print',
            'payload_version' => 'v1',
            'payload' => base64_encode($buf),
            'status' => 'pending',
            'attempts' => 0,
        ]);

        $this->executePrintJob($printJob, $printer, $buf);

        return $printJob;
    }

    /**
     * Build ESC/POS byte sequence for an order receipt.
     */
    public function buildReceiptEscPosPayload(Order $order, Printer $printer, bool $isReprint = false, string $type = 'standard'): string
    {
        $cols = $printer->columns ?: 42;
        $branch = $order->branch;

        $buffer = EscPosService::INIT;

        // Kick cash drawer if authorized cash sale
        if ($printer->has_cash_drawer && $order->payments()->where('payment_method', 'cash')->exists()) {
            $buffer .= EscPosService::CASH_DRAWER_PIN2;
        }

        // Header
        $headerTitle = (string) AppSetting::get('receipt_header_title') ?: ($branch->name ?: 'NEXTDNA RETAIL');
        $buffer .= EscPosService::ALIGN_CENTER;
        $buffer .= EscPosService::TEXT_DOUBLE_SIZE.EscPosService::TEXT_BOLD_ON;
        $buffer .= $headerTitle."\n";
        $buffer .= EscPosService::TEXT_NORMAL.EscPosService::TEXT_BOLD_OFF;

        if ($branch->address) {
            $buffer .= $branch->address."\n";
        }
        if ($branch->phone) {
            $buffer .= "Tel: {$branch->phone}\n";
        }
        if ($branch->gstin) {
            $buffer .= "GSTIN: {$branch->gstin}\n";
        }

        if ($isReprint) {
            $buffer .= EscPosService::TEXT_BOLD_ON."*** DUPLICATE / REPRINT ***\n".EscPosService::TEXT_BOLD_OFF;
        }

        $buffer .= $this->escpos->divider('=', $cols);

        // Invoice Meta
        $buffer .= EscPosService::ALIGN_LEFT;
        $invDate = ($order->invoice_date ?? $order->created_at ?? now())->format('d/m/Y H:i');
        $cashierName = $order->cashier->name ?? 'Cashier';
        $buffer .= $this->escpos->formatTwoColumns("Inv: {$order->invoice_number}", $invDate, $cols);
        $buffer .= $this->escpos->formatTwoColumns("Cashier: {$cashierName}", 'Counter: '.($order->counter->name ?? 'Main'), $cols);

        if ($order->customer) {
            $buffer .= "Customer: {$order->customer->name} (".($order->customer->phone ?? 'N/A').")\n";
        }

        $buffer .= $this->escpos->divider('-', $cols);

        // Column Titles
        $buffer .= EscPosService::TEXT_BOLD_ON;
        if ($cols <= 32) {
            $buffer .= $this->escpos->formatTwoColumns('Item', 'Qty   Amount', $cols);
        } else {
            $buffer .= $this->escpos->formatTwoColumns('Item (Size/Color)', 'Qty x Rate     Amount', $cols);
        }
        $buffer .= EscPosService::TEXT_BOLD_OFF;
        $buffer .= $this->escpos->divider('-', $cols);

        // Line Items
        foreach ($order->items as $item) {
            $variantDetails = $item->variant_details ?? [];
            $size = $variantDetails['size'] ?? '';
            $color = $variantDetails['color'] ?? '';
            $itemName = $item->product_name;

            if ($size || $color) {
                $itemName .= " ({$size}/{$color})";
            }

            if ($type === 'gift') {
                // Gift receipt omits prices
                $buffer .= $this->escpos->formatTwoColumns($itemName, "Qty: {$item->quantity}", $cols);
            } else {
                $qtyRate = "{$item->quantity} x ".number_format($item->unit_selling_price, 2);
                $lineAmt = number_format($item->line_total, 2);

                if ($cols <= 32) {
                    $buffer .= "{$itemName}\n";
                    $buffer .= $this->escpos->formatTwoColumns("  {$qtyRate}", $lineAmt, $cols);
                } else {
                    $buffer .= $this->escpos->formatTwoColumns($itemName, "{$qtyRate}  {$lineAmt}", $cols);
                }
            }
        }

        $buffer .= $this->escpos->divider('-', $cols);

        // Totals (omit in gift receipt)
        if ($type !== 'gift') {
            $buffer .= $this->escpos->formatTwoColumns('Subtotal:', number_format($order->subtotal, 2), $cols);

            if ($order->total_discount > 0) {
                $buffer .= $this->escpos->formatTwoColumns('Total Discount:', '-'.number_format($order->total_discount, 2), $cols);
            }

            if ($order->cgst_total > 0 || $order->sgst_total > 0) {
                $buffer .= $this->escpos->formatTwoColumns('CGST Total:', number_format($order->cgst_total, 2), $cols);
                $buffer .= $this->escpos->formatTwoColumns('SGST Total:', number_format($order->sgst_total, 2), $cols);
            }
            if ($order->igst_total > 0) {
                $buffer .= $this->escpos->formatTwoColumns('IGST Total:', number_format($order->igst_total, 2), $cols);
            }

            if (abs($order->round_off_amount) > 0) {
                $buffer .= $this->escpos->formatTwoColumns('Round Off:', number_format($order->round_off_amount, 2), $cols);
            }

            $buffer .= EscPosService::TEXT_BOLD_ON.EscPosService::TEXT_DOUBLE_HEIGHT;
            $buffer .= $this->escpos->formatTwoColumns('GRAND TOTAL:', 'INR '.number_format($order->grand_total, 2), $cols);
            $buffer .= EscPosService::TEXT_NORMAL.EscPosService::TEXT_BOLD_OFF;

            $buffer .= $this->escpos->divider('-', $cols);

            // Payments Breakdown
            foreach ($order->payments as $payment) {
                $methodName = strtoupper(str_replace('_', ' ', $payment->payment_method));
                $buffer .= $this->escpos->formatTwoColumns("Paid via {$methodName}:", number_format($payment->amount, 2), $cols);
            }

            if ($order->change_amount > 0) {
                $buffer .= $this->escpos->formatTwoColumns('Change Due:', number_format($order->change_amount, 2), $cols);
            }
            if ($order->balance_amount > 0) {
                $buffer .= EscPosService::TEXT_BOLD_ON;
                $buffer .= $this->escpos->formatTwoColumns('Balance Due (Credit):', number_format($order->balance_amount, 2), $cols);
                $buffer .= EscPosService::TEXT_BOLD_OFF;
            }
        }

        $buffer .= $this->escpos->divider('=', $cols);

        // Footer & Terms
        $footerMsg = (string) AppSetting::get('receipt_footer_message') ?: "Exchange within 15 days with tags & bill.\nThank you for shopping with us!";
        $buffer .= EscPosService::ALIGN_CENTER;
        $buffer .= $footerMsg."\n";

        if ((bool) AppSetting::get('receipt_show_powered_by', true)) {
            $buffer .= "Powered by NextDNA Technologies\n\n";
        } else {
            $buffer .= "\n";
        }

        // Feed & Cut
        if ($printer->has_cutter) {
            $buffer .= EscPosService::CUT_FEED_AND_CUT;
        } else {
            $buffer .= str_repeat("\n", $printer->feed_lines ?: 3);
        }

        return $buffer;
    }

    /**
     * Build ESC/POS byte sequence for a return credit note receipt.
     */
    public function buildReturnReceiptEscPosPayload(ReturnOrder $returnOrder, Printer $printer, bool $isReprint = false): string
    {
        $cols = $printer->columns ?: 42;
        $branch = $returnOrder->branch;

        $buffer = EscPosService::INIT;
        $buffer .= EscPosService::ALIGN_CENTER;
        $buffer .= EscPosService::TEXT_DOUBLE_SIZE.EscPosService::TEXT_BOLD_ON;
        $buffer .= ($branch->name ?: 'NEXTDNA RETAIL')."\n";
        $buffer .= EscPosService::TEXT_NORMAL.EscPosService::TEXT_BOLD_OFF;
        $buffer .= "CREDIT NOTE / RETURN VOUCHER\n";

        if ($isReprint) {
            $buffer .= EscPosService::TEXT_BOLD_ON."*** DUPLICATE / REPRINT ***\n".EscPosService::TEXT_BOLD_OFF;
        }

        $buffer .= $this->escpos->divider('=', $cols);
        $buffer .= EscPosService::ALIGN_LEFT;
        $returnDate = ($returnOrder->return_date ?? $returnOrder->created_at ?? now())->format('d/m/Y H:i');
        $buffer .= $this->escpos->formatTwoColumns("Return #: {$returnOrder->return_number}", $returnDate, $cols);
        if ($returnOrder->originalOrder) {
            $buffer .= "Original Inv: {$returnOrder->originalOrder->invoice_number}\n";
        }
        $buffer .= 'Customer: '.($returnOrder->customer->name ?? 'Walk-in')."\n";
        $processedBy = $returnOrder->user->name ?? 'Staff';
        $buffer .= "Processed by: {$processedBy}\n";
        $buffer .= $this->escpos->divider('-', $cols);

        foreach ($returnOrder->items as $item) {
            $variant = $item->variant;
            $name = "{$variant->product->name} ({$variant->size}/{$variant->color})";
            $line = "{$item->quantity} x ".number_format($item->unit_refund_price, 2).' = '.number_format($item->line_refund_total, 2);
            $buffer .= $this->escpos->formatTwoColumns($name, $line, $cols);
        }

        $buffer .= $this->escpos->divider('-', $cols);
        $buffer .= EscPosService::TEXT_BOLD_ON;
        $buffer .= $this->escpos->formatTwoColumns('TOTAL REFUND:', 'INR '.number_format($returnOrder->total_refund_amount, 2), $cols);
        $buffer .= $this->escpos->formatTwoColumns('Refund Method:', strtoupper($returnOrder->refund_method), $cols);
        $buffer .= EscPosService::TEXT_BOLD_OFF;
        $buffer .= $this->escpos->divider('=', $cols);

        $buffer .= EscPosService::ALIGN_CENTER;
        $buffer .= "Return reason: {$returnOrder->reason}\n";
        $buffer .= "Powered by NextDNA Technologies\n\n";

        if ($printer->has_cutter) {
            $buffer .= EscPosService::CUT_FEED_AND_CUT;
        } else {
            $buffer .= str_repeat("\n", $printer->feed_lines ?: 3);
        }

        return $buffer;
    }

    /**
     * Build ESC/POS or TSPL label payload for a product variant hangtag.
     */
    public function buildBarcodeLabelPayload(ProductVariant $variant, int $copies, Printer $printer): string
    {
        $product = $variant->product;
        $storeName = (string) AppSetting::get('label_store_name', AppSetting::get('store_name', 'NEXTDNA RETAIL'));

        // Native TSPL command generation for TVS LP 46 Dlite & TSC thermal label printers
        if ($printer->capability_profile === 'tvs_lp46') {
            $widthMm = $printer->paper_width_mm ?: 50;
            $heightMm = 38;
            $copiesCount = max(1, $copies);

            $tspl = "SIZE {$widthMm} mm, {$heightMm} mm\r\n";
            $tspl .= "GAP 2 mm, 0 mm\r\n";
            $tspl .= "DIRECTION 1\r\n";
            $tspl .= "CLS\r\n";
            $tspl .= 'TEXT 20,16,"3",0,1,1,"'.addslashes($storeName)."\"\r\n";
            $tspl .= 'TEXT 20,52,"2",0,1,1,"'.addslashes(substr($product->name, 0, 24))."\"\r\n";
            $tspl .= 'TEXT 20,80,"1",0,1,1,"Size: '.$variant->size.' | Color: '.$variant->color."\"\r\n";
            $tspl .= 'BARCODE 20,110,"128",48,1,0,2,2,"'.$variant->barcode."\"\r\n";
            $tspl .= 'TEXT 20,190,"2",0,1,1,"MRP: INR '.number_format($variant->mrp, 2).'  SP: INR '.number_format($variant->selling_price, 2)."\"\r\n";
            $tspl .= "PRINT {$copiesCount},1\r\n";

            return $tspl;
        }

        $cols = $printer->columns ?: 42;

        $buffer = EscPosService::INIT;
        $buffer .= EscPosService::ALIGN_CENTER;
        $buffer .= EscPosService::TEXT_BOLD_ON.$product->name."\n".EscPosService::TEXT_BOLD_OFF;
        $buffer .= "Size: {$variant->size}  |  Color: {$variant->color}\n";
        $buffer .= "SKU: {$variant->sku}\n";
        $buffer .= $this->escpos->barcode($variant->barcode, 45)."\n";
        $buffer .= EscPosService::TEXT_BOLD_ON;
        $buffer .= 'MRP: INR '.number_format($variant->mrp, 2).'  ';
        $buffer .= 'SP: INR '.number_format($variant->selling_price, 2)."\n";
        $buffer .= EscPosService::TEXT_BOLD_OFF;
        $buffer .= str_repeat("\n", 2);

        if ($printer->has_cutter) {
            $buffer .= EscPosService::CUT_FEED_AND_CUT;
        }

        return str_repeat($buffer, max(1, $copies));
    }

    /**
     * Build ESC/POS or TSPL batch payload for an entire label print queue supporting 1-Up and 2-Up rolls.
     *
     * @param  array<int, int>  $queue
     */
    public function buildBarcodeQueuePayload(array $queue, Printer $printer, int $columns = 2, int $widthMm = 50, int $heightMm = 25): string
    {
        $storeName = (string) AppSetting::get('label_store_name', AppSetting::get('store_name', 'NEXTDNA RETAIL'));

        // Flatten queue into array of ProductVariant models
        $flatVariants = [];
        foreach ($queue as $variantId => $copies) {
            $variant = ProductVariant::with(['product', 'product.brand'])->find($variantId);
            if ($variant) {
                for ($c = 0; $c < $copies; $c++) {
                    $flatVariants[] = $variant;
                }
            }
        }

        if (empty($flatVariants)) {
            return '';
        }

        if ($printer->capability_profile === 'tvs_lp46') {
            $rollWidthMm = ($columns === 2) ? 104 : ($printer->paper_width_mm ?: $widthMm);
            $tspl = "SIZE {$rollWidthMm} mm, {$heightMm} mm\r\n";
            $tspl .= "GAP 2 mm, 0 mm\r\n";
            $tspl .= "DIRECTION 1\r\n";

            if ($columns === 2) {
                // Chunk into pairs (Row by Row for dual sticker roll)
                $rows = array_chunk($flatVariants, 2);
                foreach ($rows as $row) {
                    $tspl .= "CLS\r\n";
                    // Left Label (X=16 dots)
                    $vLeft = $row[0];
                    $tspl .= $this->buildSingleTsplLabelCommands($vLeft, 16, $storeName);

                    // Right Label (X=424 dots)
                    if (isset($row[1])) {
                        $vRight = $row[1];
                        $tspl .= $this->buildSingleTsplLabelCommands($vRight, 424, $storeName);
                    }
                    $tspl .= "PRINT 1,1\r\n";
                }
            } else {
                // Single Column 1-Up roll
                foreach ($flatVariants as $v) {
                    $tspl .= "CLS\r\n";
                    $tspl .= $this->buildSingleTsplLabelCommands($v, 16, $storeName);
                    $tspl .= "PRINT 1,1\r\n";
                }
            }

            return $tspl;
        }

        // Fallback ESC/POS
        $buffer = '';
        foreach ($flatVariants as $v) {
            $buffer .= $this->buildBarcodeLabelPayload($v, 1, $printer);
        }

        return $buffer;
    }

    /**
     * Helper to render TSPL commands for a single label at specified X dot coordinate.
     */
    protected function buildSingleTsplLabelCommands(ProductVariant $variant, int $x, string $storeName): string
    {
        $product = $variant->product;
        $brand = $product->brand?->name ?: $storeName;
        $nameShort = substr($product->name, 0, 20);

        $out = "TEXT {$x},6,\"3\",0,1,1,\"".addslashes($brand)."  SIZE: {$variant->size}\"\r\n";
        $out .= "TEXT {$x},36,\"3\",0,1,1,\"".addslashes($nameShort)."\"\r\n";
        $out .= "BARCODE {$x},68,\"128\",68,1,0,2,2,\"{$variant->barcode}\"\r\n";
        $out .= "TEXT {$x},144,\"2\",0,1,1,\"#{$product->article_code}  {$variant->barcode}\"\r\n";
        $out .= "TEXT {$x},170,\"4\",0,1,1,\"Rs.".number_format($variant->selling_price, 0).'  MRP:'.number_format($variant->mrp, 0)."\"\r\n";

        return $out;
    }

    /**
     * Resolve default printer for a branch and purpose ('receipt' or 'label').
     */
    public function resolveDefaultPrinter(int $branchId, string $purpose = 'receipt'): Printer
    {
        $query = Printer::where('branch_id', $branchId)->where('is_active', true);

        if ($purpose === 'label') {
            $printer = $query->where('is_default_label', true)->first();
        } else {
            $printer = $query->where('is_default_receipt', true)->first();
        }

        if (! $printer) {
            $printer = Printer::where('branch_id', $branchId)->where('is_active', true)->first();
        }

        if (! $printer) {
            // Create fallback mock/browser printer
            $printer = Printer::create([
                'branch_id' => $branchId,
                'name' => 'Default Browser Printer (80mm)',
                'connection_type' => 'browser',
                'capability_profile' => 'generic_escpos',
                'paper_width_mm' => 80,
                'columns' => 42,
                'dpi' => 203,
                'has_cutter' => true,
                'has_cash_drawer' => true,
                'is_default_receipt' => true,
                'is_active' => true,
            ]);
        }

        return $printer;
    }

    /**
     * Execute print job against target driver with safe error handling.
     */
    protected function executePrintJob(PrintJob $job, Printer $printer, string $rawPayload): void
    {
        $job->increment('attempts');

        switch ($printer->connection_type) {
            case 'mock':
                // Simulator adapter for automated tests
                $job->update([
                    'status' => 'success',
                    'printed_at' => now(),
                    'error_message' => null,
                ]);
                break;

            case 'browser':
            case 'qz_tray':
                // Submitted for client-side consumption
                $job->update([
                    'status' => 'submitted',
                    'printed_at' => now(),
                ]);
                break;

            case 'lan_escpos':
                // Network socket connection
                $this->sendToLanPrinter($job, $printer, $rawPayload);
                break;

            default:
                $job->update([
                    'status' => 'submitted',
                    'printed_at' => now(),
                ]);
        }
    }

    /**
     * Direct LAN ESC/POS network socket printing.
     */
    protected function sendToLanPrinter(PrintJob $job, Printer $printer, string $rawPayload): void
    {
        if (empty($printer->ip_address)) {
            $job->update([
                'status' => 'failed',
                'error_message' => 'LAN printer IP address is not configured.',
            ]);

            return;
        }

        $port = $printer->port ?: 9100;
        $timeout = 3; // seconds

        try {
            $socket = @fsockopen($printer->ip_address, $port, $errno, $errstr, $timeout);
            if (! $socket) {
                throw new Exception("Could not connect to printer at {$printer->ip_address}:{$port}. Error: {$errstr} ({$errno})");
            }

            fwrite($socket, $rawPayload);
            fflush($socket);
            fclose($socket);

            $job->update([
                'status' => 'success',
                'printed_at' => now(),
                'error_message' => null,
            ]);
        } catch (Exception $e) {
            Log::warning("LAN print failed for Job #{$job->id}: ".$e->getMessage());
            $job->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
