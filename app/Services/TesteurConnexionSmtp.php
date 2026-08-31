<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;

class TesteurConnexionSmtp
{
    public function __construct(private ConfigurateurTransportSmtp $configurateur) {}

    /** @throws TransportExceptionInterface */
    public function tester(): void
    {
        $this->configurateur->appliquerReglages();

        $transport = Mail::mailer('smtp')->getSymfonyTransport();

        if ($transport instanceof SmtpTransport) {
            $transport->start();
            $transport->stop();
        }
    }
}
