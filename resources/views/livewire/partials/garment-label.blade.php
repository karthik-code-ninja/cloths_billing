@php $labelVariant = $labelItem['model']; @endphp
<div class="garment-label-card">
    <div class="garment-label-content">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; line-height: 1.2;">
            @if ($showBrand)
                <span style="font-size: 7pt; font-weight: 900; text-transform: uppercase; color: #000; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 75%; letter-spacing: 0.3px;">
                    {{ $labelVariant->product->brand->name ?? $customStoreName }}
                </span>
            @else <span></span> @endif
            @if ($showSizeColor)
                <span style="font-size: 7pt; font-weight: 900; background: #000; color: #fff; padding: 0 1mm; border-radius: 2px; line-height: 1;">
                    {{ $labelVariant->size }}
                </span>
            @endif
        </div>

        <div style="font-size: 7pt; font-weight: 900; color: #000; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; text-align: left; line-height: 1.2; align-self: center;">
            {{ $labelVariant->product->name }}
        </div>

        <div style="width: 100%; margin: 0; display: flex; justify-content: center; align-items: center; overflow: hidden; height: 6mm; flex-shrink: 0;">
            {!! $labelItem['barcode_svg'] !!}
        </div>

        @if ($showBarcodeText)
            <div style="font-size: 6pt; font-family: monospace, Courier, monospace; font-weight: 900; color: #000; letter-spacing: 0.4px; text-align: center; line-height: 1;">
                {{ $labelVariant->barcode ?: $labelVariant->sku }}
            </div>
        @endif

        <div style="display: flex; justify-content: space-between; align-items: baseline; width: 100%; border-top: 0.35mm solid #000; padding-top: 0.3mm; line-height: 1; box-sizing: border-box;">
            <div style="font-size: 6pt; font-weight: 800; color: #000; text-align: left; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 48%;">
                @if ($showArticleCode)
                    <span>#{{ $labelVariant->product->article_code }}</span>
                @endif
            </div>

            <div style="display: flex; flex-wrap: wrap; justify-content: flex-end; align-items: baseline; gap: 0.5mm; text-align: right; min-width: 0;">
                @if ($showMrp && $labelVariant->mrp > $labelVariant->selling_price)
                    <span style="font-size: 6pt; font-weight: 700; color: #444; text-decoration: line-through; margin-right: 0.8mm;">
                        ₹{{ number_format($labelVariant->mrp, 0) }}
                    </span>
                @endif
                @if ($showSellingPrice)
                    <span style="font-size: 12pt; font-weight: 900; color: #000; line-height: 1;">
                        ₹{{ number_format($labelVariant->selling_price, 0) }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
