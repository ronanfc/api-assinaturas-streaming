<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DescontoPagamentoAntecipado extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $dueDate;
    public $discountUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $dueDate, $discountUrl)
    {
        $this->user = $user;
        $this->dueDate = $dueDate;
        $this->discountUrl = $discountUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Lembrete: Sua assinatura está vencendo em breve. Aproveito o desconto!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.desconto-pagamento-antecipado',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
