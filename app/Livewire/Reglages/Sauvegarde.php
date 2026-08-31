<?php

namespace App\Livewire\Reglages;

use App\Exceptions\SauvegardeInvalide;
use App\Services\EtatSauvegarde;
use App\Services\SauvegardeApplication;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Sauvegarde extends Component
{
    use WithFileUploads;

    public ?TemporaryUploadedFile $archive = null;

    public ?string $message = null;

    public ?string $erreur = null;

    public function restaurer(SauvegardeApplication $sauvegarde)
    {
        $this->validate(
            ['archive' => ['required', 'file', 'extensions:zip', 'max:512000']],
            [],
            ['archive' => 'sauvegarde'],
        );

        try {
            $sauvegarde->restaurer($this->archive->getRealPath());
        } catch (SauvegardeInvalide $exception) {
            $this->erreur = $exception->getMessage();
            $this->reset('archive');

            return null;
        }

        session()->flash('message', 'Sauvegarde restaurée.');

        return $this->redirectRoute('reglages');
    }

    public function render()
    {
        return view('livewire.reglages.sauvegarde', [
            'derniereExportation' => app(EtatSauvegarde::class)->derniereExportation(),
        ]);
    }
}
