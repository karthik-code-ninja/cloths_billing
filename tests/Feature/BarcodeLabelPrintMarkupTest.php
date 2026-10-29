<?php

namespace Tests\Feature;

use App\Livewire\BarcodeLabelPrinter;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\BarcodeService;
use Illuminate\Http\Request;
use Laravel\Boost\Middleware\InjectBoost;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class BarcodeLabelPrintMarkupTest extends TestCase
{
    public function test_browser_logging_injection_preserves_the_label_print_button(): void
    {
        $viewData = array_merge(get_object_vars(new BarcodeLabelPrinter), [
            'queuedItems' => collect(),
            'printableRows' => [],
            'totalLabelsCount' => 0,
            'printers' => collect(),
        ]);
        $markup = view('livewire.barcode-label-printer', $viewData)->render();
        $document = '<!doctype html><html><head></head><body>'.$markup.'</body></html>';
        $response = new Response($document, 200, ['Content-Type' => 'text/html']);

        $result = (new InjectBoost)->handle(
            Request::create('/billing/barcode-labels'),
            fn (Request $request): Response => $response,
        );

        $this->assertSame(1, substr_count($result->getContent(), 'id="browser-logger-active"'));
        $this->assertStringContainsString($markup, $result->getContent());
    }

    public function test_two_labels_render_one_printable_row_without_extra_labels(): void
    {
        $product = new Product(['name' => 'GRV KOREAN BAGGY', 'article_code' => '255']);
        $product->setRelation('brand', null);
        $variant = new ProductVariant([
            'size' => '32',
            'barcode' => '38628134014',
            'selling_price' => 799,
            'mrp' => 999,
        ]);
        $variant->setRelation('product', $product);
        $item = [
            'model' => $variant,
            'copies' => 2,
            'barcode_svg' => app(BarcodeService::class)->generateCode128Svg($variant->barcode, showText: false),
        ];
        $viewData = array_merge(get_object_vars(new BarcodeLabelPrinter), [
            'queuedItems' => collect(),
            'printableRows' => [[$item, $item]],
            'totalLabelsCount' => 2,
            'printers' => collect(),
        ]);

        $markup = view('livewire.barcode-label-printer', $viewData)->render();
        $printableMarkup = explode('<div class="label-print-container">', $markup, 2)[1];

        $this->assertSame(1, substr_count($printableMarkup, 'class="label-print-row-2up"'));
        $this->assertSame(2, substr_count($printableMarkup, 'class="garment-label-card"'));
        $this->assertSame(2, substr_count($printableMarkup, 'GRV KOREAN BAGGY'));
        $this->assertStringContainsString('Print job: 2 labels · 1 print page', $markup);
    }
}
