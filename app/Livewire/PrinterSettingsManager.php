<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Counter;
use App\Models\Printer;
use App\Models\PrintJob;
use App\Services\PrintService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class PrinterSettingsManager extends Component
{
    use WithPagination;

    public string $tab = 'printers'; // printers, print_jobs, qz_tray_guide

    // Add / Edit Printer Wizard Modal
    public bool $showPrinterModal = false;

    public ?int $editingPrinterId = null;

    // Wizard Step state (1: General & Location, 2: Hardware & Connection, 3: Paper & Capabilities)
    public int $wizardStep = 1;

    public string $name = 'Counter Thermal Printer';

    public int $branch_id = 1;

    public ?int $counter_id = null;

    public string $connection_type = 'browser'; // browser, qz_tray, lan_escpos, mock

    public string $device_identifier = 'EPSON_TM_T82';

    public string $ip_address = '192.168.1.100';

    public int $port = 9100;

    public string $capability_profile = 'epson_standard';

    public int $paper_width_mm = 80;

    public int $columns = 42;

    public int $dpi = 203;

    public int $feed_lines = 3;

    public bool $has_cutter = true;

    public bool $has_cash_drawer = true;

    public bool $is_default_receipt = false;

    public bool $is_default_label = false;

    // Test print status feedback
    public ?string $testPrintStatus = null;

    public function mount()
    {
        $this->branch_id = Auth::user()->branch_id ?: (Branch::first()->id ?? 1);
        $this->counter_id = Counter::where('branch_id', $this->branch_id)->first()->id ?? null;
    }

    public function render()
    {
        $printers = Printer::with(['branch', 'counter'])->get();
        $printJobs = PrintJob::with(['printer', 'order'])->latest()->paginate(15);
        $branches = Branch::all();
        $counters = Counter::where('branch_id', $this->branch_id)->get();

        return view('livewire.printer-settings-manager', [
            'printers' => $printers,
            'printJobs' => $printJobs,
            'branches' => $branches,
            'counters' => $counters,
        ]);
    }

    public function openAddPrinterModal()
    {
        $this->reset([
            'editingPrinterId', 'name', 'connection_type', 'device_identifier',
            'ip_address', 'is_default_receipt', 'is_default_label',
        ]);
        $this->wizardStep = 1;
        $this->name = 'New Thermal Receipt Printer';
        $this->paper_width_mm = 80;
        $this->columns = 42;
        $this->connection_type = 'browser';
        $this->has_cutter = true;
        $this->has_cash_drawer = true;
        $this->showPrinterModal = true;
    }

    public function editPrinter(int $printerId)
    {
        $p = Printer::findOrFail($printerId);
        $this->editingPrinterId = $p->id;
        $this->name = $p->name;
        $this->branch_id = $p->branch_id;
        $this->counter_id = $p->counter_id;
        $this->connection_type = $p->connection_type;
        $this->device_identifier = $p->device_identifier ?: '';
        $this->ip_address = $p->ip_address ?: '';
        $this->port = $p->port;
        $this->capability_profile = $p->capability_profile;
        $this->paper_width_mm = $p->paper_width_mm;
        $this->columns = $p->columns;
        $this->dpi = $p->dpi;
        $this->feed_lines = $p->feed_lines;
        $this->has_cutter = $p->has_cutter;
        $this->has_cash_drawer = $p->has_cash_drawer;
        $this->is_default_receipt = $p->is_default_receipt;
        $this->is_default_label = $p->is_default_label;

        $this->wizardStep = 1;
        $this->showPrinterModal = true;
    }

    public function updatedPaperWidthMm($val)
    {
        if ((int) $val === 58) {
            $this->columns = 32;
        } else {
            $this->columns = 42;
        }
    }

    public function nextStep()
    {
        if ($this->wizardStep === 1) {
            $this->validate([
                'name' => 'required|string|min:3',
                'branch_id' => 'required|exists:branches,id',
            ]);
        }
        $this->wizardStep++;
    }

    public function prevStep()
    {
        if ($this->wizardStep > 1) {
            $this->wizardStep--;
        }
    }

    public function savePrinter()
    {
        $this->validate([
            'name' => 'required|string|min:3',
            'branch_id' => 'required|exists:branches,id',
            'connection_type' => 'required|in:browser,qz_tray,lan_escpos,mock',
            'paper_width_mm' => 'required|in:50,58,80,104',
            'columns' => 'required|integer',
        ]);

        // If setting as default receipt, clear others in this branch
        if ($this->is_default_receipt) {
            Printer::where('branch_id', $this->branch_id)->update(['is_default_receipt' => false]);
        }
        if ($this->is_default_label) {
            Printer::where('branch_id', $this->branch_id)->update(['is_default_label' => false]);
        }

        $data = [
            'name' => $this->name,
            'branch_id' => $this->branch_id,
            'counter_id' => $this->counter_id,
            'connection_type' => $this->connection_type,
            'device_identifier' => $this->device_identifier,
            'ip_address' => $this->ip_address,
            'port' => $this->port,
            'capability_profile' => $this->capability_profile,
            'paper_width_mm' => $this->paper_width_mm,
            'columns' => $this->columns,
            'dpi' => $this->dpi,
            'feed_lines' => $this->feed_lines,
            'has_cutter' => $this->has_cutter,
            'has_cash_drawer' => $this->has_cash_drawer,
            'is_default_receipt' => $this->is_default_receipt,
            'is_default_label' => $this->is_default_label,
            'is_active' => true,
        ];

        if ($this->editingPrinterId) {
            Printer::findOrFail($this->editingPrinterId)->update($data);
            session()->flash('success', 'Printer updated successfully.');
        } else {
            Printer::create($data);
            session()->flash('success', 'New thermal printer added successfully.');
        }

        $this->showPrinterModal = false;
    }

    public function testPrintDiagnostic(int $printerId)
    {
        $printer = Printer::findOrFail($printerId);
        $printService = app(PrintService::class);

        try {
            $job = $printService->testPrint($printer);
            if ($job->status === 'failed') {
                session()->flash('error', "Test print failed: {$job->error_message}");
            } else {
                session()->flash('success', "Test print dispatched to '{$printer->name}' ({$printer->connection_type}). Status: {$job->status}");
            }
        } catch (Exception $e) {
            session()->flash('error', 'Diagnostics error: '.$e->getMessage());
        }
    }

    public function reprintJob(int $jobId)
    {
        $job = PrintJob::with(['printer', 'order'])->findOrFail($jobId);
        if ($job->order) {
            $printService = app(PrintService::class);
            $newJob = $printService->printOrderReceipt($job->order, $job->printer, true);
            session()->flash('success', "Dispatched reprint #{$newJob->id} marked as DUPLICATE / REPRINT.");
        } else {
            session()->flash('error', 'Cannot reprint a job without an associated invoice order.');
        }
    }

    public function togglePrinterStatus(int $printerId)
    {
        $printer = Printer::findOrFail($printerId);
        $printer->update(['is_active' => ! $printer->is_active]);
        session()->flash('success', "Printer '{$printer->name}' is now ".($printer->is_active ? 'Active' : 'Disabled').'.');
    }

    public function deletePrinter(int $printerId)
    {
        $printer = Printer::findOrFail($printerId);

        // If this printer was default, reassign default to another active printer in the same branch
        if ($printer->is_default_receipt || $printer->is_default_label) {
            $replacement = Printer::where('branch_id', $printer->branch_id)
                ->where('id', '!=', $printer->id)
                ->where('is_active', true)
                ->first();

            if ($replacement) {
                if ($printer->is_default_receipt) {
                    $replacement->update(['is_default_receipt' => true]);
                }
                if ($printer->is_default_label) {
                    $replacement->update(['is_default_label' => true]);
                }
            }
        }

        $printerName = $printer->name;
        DB::transaction(function () use ($printer) {
            $printer->printJobs()->delete();
            $printer->delete();
        });

        session()->flash('success', "Printer '{$printerName}' removed successfully.");
    }
}
