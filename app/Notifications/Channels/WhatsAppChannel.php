<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Placeholder channel. A plant can point PACKFLOW_WHATSAPP_WEBHOOK
 * at their gateway later; until then the message is written to the log.
 */
class WhatsAppChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $payload = $notification->toWhatsApp($notifiable);
        $webhook = config('services.whatsapp.webhook');

        Log::info('packflow.whatsapp', $payload);

        if ($webhook) {
            try {
                \Illuminate\Support\Facades\Http::timeout(5)->post($webhook, $payload);
            } catch (\Throwable $e) {
                Log::warning('packflow.whatsapp.failed', ['error' => $e->getMessage()]);
            }
        }
    }
}
