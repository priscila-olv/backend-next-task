<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InviteProjectMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $mailData;

    public function __construct($mailData)
    {
        $this->mailData = $mailData;
    }
    public function build()
    {
        return $this->subject($this->mailData['subject'])
        ->view('inviteProject')
        ->with('name_user', $this->mailData['name_user']) 
        ->with('token', $this->mailData['token'])
        ->with('project_name', $this->mailData['project_name']);
    }
}
