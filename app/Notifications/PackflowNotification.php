<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PackflowNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public ?string $url = null,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->email ?? false) {
            $channels[] = 'mail';
        }

        if (config('packflow.notifications.whatsapp') && ($notifiable->phone ?? false)) {
            $channels[] = \App\Notifications\Channels\WhatsAppChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)->subject($this->title)->line($this->body);

        if ($this->url) {
            $mail->action('Open in PackFlow', $this->url);
        }

        return $mail;
    }

    /** @return array{to: string, body: string} */
    public function toWhatsApp(object $notifiable): array
    {
        return [
            'to' => (string) $notifiable->phone,
            'body' => $this->title.' — '.$this->body,
        ];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
        ];
    }
}
