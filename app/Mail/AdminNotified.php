<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Blade;

class AdminNotified extends AppMail
{
    public User $createdUser;

    public function __construct(User $createdUser)
    {
        $this->createdUser = $createdUser;
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
            subject: config('app.name').' - New User created',
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
            '# New User created',
            '',
            'The user {{ $user->name }} ({{ $user->email }}) was just created.',
        ];

        return Blade::render(
            implode("\n", $bladeLines),
            [
                'user' => $this->createdUser->setVisible(['name', 'email']),
                ...$this->getLayoutData(),
            ]
        );
    }
}
