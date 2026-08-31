<?php

namespace App\Mail;

use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CarteAdherentMail extends Mailable
{
    public function __construct(
        public string $objet,
        public string $corpsHtml,
        public string $corpsTexte,
        public string $cheminPng,
        public string $cheminPdf,
        public string $nomFichierPdf,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->objet);
    }

    public function content(): Content
    {
        return new Content(
            html: 'mails.carte-adherent',
            text: 'mails.carte-adherent-texte',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->cheminPdf)->as($this->nomFichierPdf)->withMime('application/pdf'),
        ];
    }
}
