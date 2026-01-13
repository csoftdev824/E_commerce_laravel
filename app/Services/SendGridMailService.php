<?php

namespace App\Services;

use Illuminate\Support\Facades\View;
use SendGrid;
use SendGrid\Mail\Mail;
use SendGrid\Mail\TypeException;

class SendGridMailService
{
    protected SendGrid $sendGrid;

    public function __construct()
    {
        $apiKey = env('SENDGRID_API_KEY');
        
        if (empty($apiKey)) {
            throw new \RuntimeException('SENDGRID_API_KEY is not set in environment variables.');
        }

        $this->sendGrid = new SendGrid($apiKey);
    }

    /**
     * Send an email using SendGrid
     *
     * @param string $to
     * @param string $subject
     * @param string $content
     * @param string|null $fromEmail
     * @param string|null $fromName
     * @return bool
     * @throws TypeException
     */
    public function send(
        string $to,
        string $subject,
        string $content,
        ?string $fromEmail = null,
        ?string $fromName = null
    ): bool {
        $fromEmail = $fromEmail ?? 'aninda@evra.solutions';
        $fromName = $fromName ?? config('mail.from.name', 'E-commerce System');

        $email = new Mail();
        $email->setFrom($fromEmail, $fromName);
        $email->setSubject($subject);
        $email->addTo($to);
        $email->addContent('text/plain', $content);

        try {
            $response = $this->sendGrid->send($email);
            
            // Check if the email was sent successfully (2xx status codes)
            $statusCode = $response->statusCode();
            $responseBody = $response->body();
            $responseHeaders = $response->headers();
            
            // Log response details for debugging
            \Log::info('SendGrid email response', [
                'status_code' => $statusCode,
                'body' => $responseBody,
                'headers' => $responseHeaders,
            ]);
            
            if ($statusCode >= 200 && $statusCode < 300) {
                \Log::info('SendGrid email sent successfully', [
                    'to' => $to,
                    'subject' => $subject,
                ]);
                return true;
            } else {
                \Log::error('SendGrid email sending failed with non-2xx status', [
                    'status_code' => $statusCode,
                    'body' => $responseBody,
                    'to' => $to,
                    'subject' => $subject,
                ]);
                return false;
            }
        } catch (\Exception $e) {
            \Log::error('SendGrid email sending exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'to' => $to,
                'subject' => $subject,
            ]);
            throw $e;
        }
    }

    /**
     * Send an email using a Blade view template
     *
     * @param string $to
     * @param string $subject
     * @param string $view
     * @param array $data
     * @param string|null $fromEmail
     * @param string|null $fromName
     * @return bool
     * @throws TypeException
     */
    public function sendView(
        string $to,
        string $subject,
        string $view,
        array $data = [],
        ?string $fromEmail = null,
        ?string $fromName = null
    ): bool {
        $content = View::make($view, $data)->render();
        return $this->send($to, $subject, $content, $fromEmail, $fromName);
    }
}
