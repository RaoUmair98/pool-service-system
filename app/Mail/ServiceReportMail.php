<?php

namespace App\Mail;

use App\Models\Visit;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Visit $visit) {}

    public function envelope(): Envelope
    {
        $company = $this->visit->company?->name ?? config('app.name');
        $date = $this->visit->scheduled_date?->format('d M Y') ?? '';

        return new Envelope(
            subject: "Service report — {$date} · {$company}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.service-report',
            with: [
                'visit' => $this->visit,
                'checklist' => $this->visit->checklistItems(),
            ],
        );
    }
}
