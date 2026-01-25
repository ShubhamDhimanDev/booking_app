<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewUserCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Your account details')
            ->view('emails.tests.new-user-credentials', [
                'email' => $this->user->email,
                'password' => $this->password,
                'loginUrl' => url('/login'),
            ]);
    }
}
