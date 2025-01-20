<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Blade;

class WelcomeEmail extends AppMail
{
    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        $this
            ->body($this->createBodyWithString());
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: config('app.name').' - Welcome',
        );
    }

    /**
     * Crete the message
     *
     * NOTE: this can also be done in a blade view, but as it is so short, we can do it here
     */
    public function createBodyWithString()
    {
        $bladeLines = [
            '# Welcome {{ $user->name }}!',
            '',
            'You have now been created on "{{$appName}}"',
        ];

        return Blade::render(
            implode("\n", $bladeLines),
            [
                'user' => $this->user->setVisible(['name', 'email']),
                ...$this->getLayoutData(),
            ]
        );
    }
}
