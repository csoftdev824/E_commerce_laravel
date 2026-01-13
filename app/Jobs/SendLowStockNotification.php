<?php

namespace App\Jobs;

use App\Services\SendGridMailService;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendLowStockNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Product $product
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(SendGridMailService $mailService): void
    {
        try {
            // Send low stock notification to admin email using SendGrid
            $recipientEmail = env('MAIL_TO_ADDRESS', 'admin@example.com');
            
            if (empty($recipientEmail)) {
                Log::error('MAIL_TO_ADDRESS is not set in environment variables.');
                return;
            }
            
            $subject = 'Low Stock Notification - ' . $this->product->name;
            $mailService->sendView(
                $recipientEmail,
                $subject,
                'emails.low-stock-notification',
                [
                    'product' => $this->product,
                ]
            );
        } catch (\Exception $e) {
            // Log the error but don't fail the job
            Log::error('Failed to send low stock notification: ' . $e->getMessage(), [
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
            ]);
            
            // Re-throw if you want the job to be retried
            // throw $e;
        }
    }
}
