<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\InventoryStock;
use App\Models\ProductVariant;
use App\Models\StockLocation;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Deduct stock for a product variant at a specific location safely.
     * Uses pessimistic locking (lockForUpdate) to handle concurrent checkout attempts.
     *
     * @throws Exception if stock is insufficient and negative stock is not allowed
     */
    public function decrementStock(
        ProductVariant $variant,
        int $quantity,
        int $stockLocationId,
        string $movementType,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $remarks = null,
        ?int $userId = null
    ): StockMovement {
        if ($quantity <= 0) {
            throw new Exception('Stock decrement quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($variant, $quantity, $stockLocationId, $movementType, $referenceType, $referenceId, $remarks, $userId) {
            // Pessimistic lock on the stock record
            $stock = InventoryStock::where('product_variant_id', $variant->id)
                ->where('stock_location_id', $stockLocationId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                $stock = InventoryStock::create([
                    'product_variant_id' => $variant->id,
                    'stock_location_id' => $stockLocationId,
                    'quantity' => 0,
                    'quantity_reserved' => 0,
                ]);
                // Re-lock
                $stock = InventoryStock::where('id', $stock->id)->lockForUpdate()->first();
            }

            $allowNegative = (bool) AppSetting::get('allow_negative_stock', false);

            if (! $allowNegative && $stock->quantity < $quantity) {
                throw new Exception(
                    "Insufficient stock for {$variant->fullName}. Available: {$stock->quantity}, Requested: {$quantity}"
                );
            }

            $newQuantity = $stock->quantity - $quantity;
            $stock->quantity = $newQuantity;
            $stock->save();

            // Record immutable stock movement ledger entry
            return StockMovement::create([
                'product_variant_id' => $variant->id,
                'stock_location_id' => $stockLocationId,
                'movement_type' => $movementType,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'quantity' => -$quantity,
                'unit_cost' => $variant->purchase_cost,
                'balance_after' => $newQuantity,
                'remarks' => $remarks,
                'user_id' => $userId,
            ]);
        });
    }

    /**
     * Add stock for a product variant at a specific location safely.
     */
    public function incrementStock(
        ProductVariant $variant,
        int $quantity,
        int $stockLocationId,
        string $movementType,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $remarks = null,
        ?int $userId = null
    ): StockMovement {
        if ($quantity <= 0) {
            throw new Exception('Stock increment quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($variant, $quantity, $stockLocationId, $movementType, $referenceType, $referenceId, $remarks, $userId) {
            $stock = InventoryStock::where('product_variant_id', $variant->id)
                ->where('stock_location_id', $stockLocationId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                $stock = InventoryStock::create([
                    'product_variant_id' => $variant->id,
                    'stock_location_id' => $stockLocationId,
                    'quantity' => 0,
                    'quantity_reserved' => 0,
                ]);
                $stock = InventoryStock::where('id', $stock->id)->lockForUpdate()->first();
            }

            $newQuantity = $stock->quantity + $quantity;
            $stock->quantity = $newQuantity;
            $stock->save();

            return StockMovement::create([
                'product_variant_id' => $variant->id,
                'stock_location_id' => $stockLocationId,
                'movement_type' => $movementType,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'quantity' => $quantity,
                'unit_cost' => $variant->purchase_cost,
                'balance_after' => $newQuantity,
                'remarks' => $remarks,
                'user_id' => $userId,
            ]);
        });
    }

    /**
     * Get or create a default stock location for a branch.
     */
    public function getDefaultLocation(int $branchId, string $type = 'retail'): StockLocation
    {
        return StockLocation::firstOrCreate(
            ['branch_id' => $branchId, 'type' => $type],
            ['name' => ucfirst($type).' Rack', 'code' => strtoupper($type).'-1', 'is_active' => true]
        );
    }
}
