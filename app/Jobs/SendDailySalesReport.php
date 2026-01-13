<?php

namespace App\Jobs;

use App\Services\SendGridMailService;
use App\Models\OrderItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendDailySalesReport implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(SendGridMailService $mailService): void
    {
        $today = now()->startOfDay();
        $tomorrow = now()->endOfDay();

        // Get all order items from today
        $orderItems = OrderItem::whereHas('order', function ($query) use ($today, $tomorrow) {
            $query->whereBetween('created_at', [$today, $tomorrow]);
        })->with(['product', 'order'])->get();

        // Group by product and calculate totals
        $salesData = $orderItems->groupBy('product_id')->map(function ($items) {
            $product = $items->first()->product;
            $totalQuantity = $items->sum('quantity');
            $totalRevenue = $items->sum(function ($item) {
                return $item->quantity * $item->price;
            });

            return [
                'product_name' => $product->name,
                'quantity_sold' => $totalQuantity,
                'revenue' => $totalRevenue,
            ];
        })->values()->toArray();

        try {
            // Send daily sales report to admin email using SendGrid
            $recipientEmail = env('MAIL_TO_ADDRESS', 'admin@example.com');
            
            if (empty($recipientEmail)) {
                Log::error('MAIL_TO_ADDRESS is not set in environment variables.');
                return;
            }
            
            $subject = 'Daily Sales Report - ' . now()->format('Y-m-d');
            $mailService->sendView(
                $recipientEmail,
                $subject,
                'emails.daily-sales-report',
                [
                    'salesData' => $salesData,
                    'date' => now()->format('Y-m-d'),
                ]
            );
        } catch (\Exception $e) {
            // Log the error but don't fail the job
            Log::error('Failed to send daily sales report: ' . $e->getMessage());
            
            // Re-throw if you want the job to be retried
            // throw $e;
        }
    }
}
