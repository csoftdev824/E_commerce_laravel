<?php

namespace App\Observers;

use App\Constants\ProductConstants;
use App\Jobs\SendLowStockNotification;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->checkLowStock($product);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Check if stock_quantity was changed and is now low
        if ($product->wasChanged('stock_quantity')) {
            $this->checkLowStock($product);
        }
    }

    /**
     * Check if product stock is low and dispatch notification.
     */
    private function checkLowStock(Product $product): void
    {
        // Get the original stock quantity before the update
        $originalStock = $product->getOriginal('stock_quantity');
        $currentStock = $product->stock_quantity;

        // Log for debugging (can be removed in production)
        Log::debug('Low stock check', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'original_stock' => $originalStock,
            'current_stock' => $currentStock,
            'threshold' => ProductConstants::LOW_STOCK_THRESHOLD,
        ]);

        // Notify if:
        // 1. Current stock is at or below threshold
        // 2. Current stock is greater than 0 (not out of stock)
        // 3. Stock was reduced (original stock was higher than current) OR stock crossed threshold
        $stockWasReduced = $originalStock !== null && $originalStock > $currentStock;
        $stockCrossedThreshold = $originalStock !== null 
            && $originalStock > ProductConstants::LOW_STOCK_THRESHOLD 
            && $currentStock <= ProductConstants::LOW_STOCK_THRESHOLD;
        $isNewProduct = $originalStock === null && $currentStock <= ProductConstants::LOW_STOCK_THRESHOLD;

        if ($currentStock <= ProductConstants::LOW_STOCK_THRESHOLD 
            && $currentStock > 0 
            && ($stockWasReduced || $stockCrossedThreshold || $isNewProduct)) {
            Log::info('Dispatching low stock notification', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'current_stock' => $currentStock,
                'original_stock' => $originalStock,
                'reason' => $isNewProduct ? 'new_product' : ($stockCrossedThreshold ? 'crossed_threshold' : 'stock_reduced'),
            ]);
            SendLowStockNotification::dispatch($product);
        } else {
            Log::debug('Low stock notification NOT dispatched', [
                'product_id' => $product->id,
                'reason' => [
                    'current_below_threshold' => $currentStock <= ProductConstants::LOW_STOCK_THRESHOLD,
                    'current_above_zero' => $currentStock > 0,
                    'stock_was_reduced' => $stockWasReduced,
                    'stock_crossed_threshold' => $stockCrossedThreshold,
                    'is_new_product' => $isNewProduct,
                ],
            ]);
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
