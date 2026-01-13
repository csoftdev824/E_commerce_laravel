<?php

namespace App\Console\Commands;

use App\Services\SendGridMailService;
use Illuminate\Console\Command;

class TestSendGrid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:sendgrid {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SendGrid email sending';

    /**
     * Execute the console command.
     */
    public function handle(SendGridMailService $mailService)
    {
        $email = $this->argument('email');
        
        $this->info("Sending test email to: {$email}");
        
        try {
            $result = $mailService->send(
                $email,
                'Test Email from Laravel',
                'This is a test email to verify SendGrid is working correctly.'
            );
            
            if ($result) {
                $this->info('✅ Email sent successfully!');
            } else {
                $this->error('❌ Email sending failed. Check logs for details.');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error('Check storage/logs/laravel.log for more details.');
        }
    }
}
