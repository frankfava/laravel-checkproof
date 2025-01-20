<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\View;

abstract class AppMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string|\Illuminate\Contracts\View\View
     */
    public $content;

    /**
     * Get the message envelope.
     */
    abstract public function envelope(): Envelope;

    /**
     * Data for the markdown template.
     */
    public function getLayoutData(): array
    {
        return array_merge(
            [
                'appName' => config('app.name'),
            ],
            $this->viewData
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            markdown: 'mail.layouts.appMail',
            with: $this->getLayoutData(),
        );
    }

    /**
     * Use a view for the Body (Markdown)
     * Can use mail component in view
     *
     * @return $this
     */
    public function body(string $view, array $data = []): static
    {
        if (View::exists($view)) {
            $this->content = view($view, array_merge($this->getLayoutData(), $data));

            return $this;
        }
        $this->content = $view;

        return $this;
    }
}
