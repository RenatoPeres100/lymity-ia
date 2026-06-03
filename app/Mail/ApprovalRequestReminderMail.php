<?php

namespace App\Mail;

use App\Models\ApprovalRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApprovalRequestReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ApprovalRequest $approval,
        public readonly User            $recipient,
        public readonly string          $panelUrl,
        public readonly array           $actionUrls,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Lembrete: aprovação pendente para {$this->approval->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.approvals.reminder',
        );
    }
}
