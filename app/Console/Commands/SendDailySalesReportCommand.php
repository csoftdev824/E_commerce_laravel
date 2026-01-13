<?php

namespace App\Console\Commands;

use App\Jobs\SendDailySalesReport;
use Illuminate\Console\Command;

class SendDailySalesReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:send-daily-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily sales report to admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        SendDailySalesReport::dispatch();
        $this->info('Daily sales report job dispatched successfully.');
    }
}
