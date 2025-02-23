<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AduanEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $recipientEmail; // Store recipient email

    /**
     * Create a new message instance.
     *
     * @param array $data
     * @param string $recipientEmail
     */
    public function __construct(array $data, string $recipientEmail)
    {
        $this->data = $data;
        $this->recipientEmail = $recipientEmail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->data['email']) // Sender's email
                    ->to($this->recipientEmail) // Dynamic recipient from request
                    ->subject('Aduan: ' . $this->data['tajuk_aduan'])
                    ->view('emails.aduan')
                    ->with('data', $this->data);
    }
}
