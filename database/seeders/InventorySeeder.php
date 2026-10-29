<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\InventoryStock;
use App\Models\ProductVariant;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('code', 'SLM01')->first() ?? Branch::first();
        if (! $branch) {
            return;
        }

        $retailLoc = StockLocation::firstOrCreate(
            ['branch_id' => $branch->id, 'code' => 'RETAIL-01'],
            ['name' => 'Main Showroom Floor', 'type' => 'retail', 'is_active' => true]
        );

        $damagedLoc = StockLocation::firstOrCreate(
            ['branch_id' => $branch->id, 'code' => 'DAMAGED-01'],
            ['name' => 'Damaged / Alteration Rack', 'type' => 'damaged', 'is_active' => true]
        );

        $admin = User::where('role', 'admin')->first();

        // Seed initial opening stock for every variant (between 15 and 35 units)
        $variants = ProductVariant::all();

        foreach ($variants as $index => $variant) {
            $initialQty = 25 + ($index % 10);

            $stock = InventoryStock::firstOrCreate(
                ['product_variant_id' => $variant->id, 'stock_location_id' => $retailLoc->id],
                ['quantity' => $initialQty, 'quantity_reserved' => 0]
            );

            // Record initial opening stock movement if not present
            $hasMovement = StockMovement::where('product_variant_id', $variant->id)
                ->where('movement_type', 'opening')
                ->exists();

            if (! $hasMovement) {
                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'stock_location_id' => $retailLoc->id,
                    'movement_type' => 'opening',
                    'reference_type' => 'OpeningBalance',
                    'reference_id' => 1,
                    'quantity' => $initialQty,
                    'unit_cost' => $variant->purchase_cost,
                    'balance_after' => $initialQty,
                    'remarks' => 'Opening Stock Migration 2026',
                    'user_id' => $admin?->id,
                ]);
            }
        }
    }
}
