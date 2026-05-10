<?php

namespace App\Mail;

use App\Models\HelpdeskTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HelpdeskTicketAdminActionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public HelpdeskTicket $ticket,
        public string $action
    ) {
    }

    public function build(): self
    {
        $no = $this->ticket->ticket_number;

        $subject = match ($this->action) {
            'reply' => 'JodohMurni — Balasan admin untuk tiket helpdesk ' . $no,
            'resolve' => 'JodohMurni — Tiket helpdesk ' . $no . ' telah diselesaikan',
            'reject' => 'JodohMurni — Tiket helpdesk ' . $no . ' telah ditolak',
            default => 'JodohMurni — Kemas kini tiket helpdesk ' . $no,
        };

        return $this
            ->subject($subject)
            ->to($this->ticket->reporter_email)
            ->view('emails.helpdesk_admin_action');
    }
}
