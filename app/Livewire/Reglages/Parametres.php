<?php

namespace App\Livewire\Reglages;

use App\Livewire\Forms\ReglagesForm;
use App\Services\TesteurConnexionSmtp;
use Livewire\Component;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class Parametres extends Component
{
    public ReglagesForm $form;

    public ?string $message = null;

    public ?string $erreur = null;

    public function mount(): void
    {
        $this->form->charger();
    }

    public function enregistrer(): void
    {
        $this->form->enregistrer();

        $this->message = 'Réglages enregistrés.';
        $this->erreur = null;
    }

    public function testerConnexionSmtp(TesteurConnexionSmtp $testeur): void
    {
        $this->form->enregistrer();

        try {
            $testeur->tester();
            $this->message = 'Réglages enregistrés. Connexion SMTP réussie.';
            $this->erreur = null;
        } catch (TransportExceptionInterface $exception) {
            $this->message = null;
            $this->erreur = 'Connexion SMTP impossible : '.$exception->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.reglages.parametres');
    }
}
