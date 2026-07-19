<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $member) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Undangan bergabung ke PaySync');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.team-invitation',
            with: ['member' => $this->member],
        );
    }
}
