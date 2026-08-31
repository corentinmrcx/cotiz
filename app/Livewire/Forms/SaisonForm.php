<?php

namespace App\Livewire\Forms;

use App\Models\Saison;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class SaisonForm extends Form
{
    public ?Saison $saison = null;

    public string $tarif_adulte = '';

    public string $tarif_enfant_famille = '';

    public string $tarif_enfant_seul = '';

    public string $couleur = Saison::COULEUR_PAR_DEFAUT;

    public ?TemporaryUploadedFile $logo = null;

    public function rules(): array
    {
        return [
            'tarif_adulte' => ['required', 'numeric', 'min:0'],
            'tarif_enfant_famille' => ['required', 'numeric', 'min:0'],
            'tarif_enfant_seul' => ['required', 'numeric', 'min:0'],
            'couleur' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'logo' => ['nullable', 'file', 'mimes:png,svg', 'max:5120'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'tarif_adulte' => 'tarif adulte',
            'tarif_enfant_famille' => 'tarif enfant en famille',
            'tarif_enfant_seul' => 'tarif enfant seul',
            'couleur' => 'couleur',
            'logo' => 'logo',
        ];
    }

    public function charger(Saison $saison): void
    {
        $this->saison = $saison;
        $this->tarif_adulte = (string) $saison->tarif_adulte;
        $this->tarif_enfant_famille = (string) $saison->tarif_enfant_famille;
        $this->tarif_enfant_seul = (string) $saison->tarif_enfant_seul;
        $this->couleur = $saison->couleur;
        $this->logo = null;
    }

    public function enregistrer(): void
    {
        $this->validate();

        $this->saison->update([
            'tarif_adulte' => $this->tarif_adulte,
            'tarif_enfant_famille' => $this->tarif_enfant_famille,
            'tarif_enfant_seul' => $this->tarif_enfant_seul,
            'couleur' => strtolower($this->couleur),
            'logo' => $this->stockerLogo($this->logo) ?? $this->saison->logo,
        ]);

        $this->charger($this->saison->fresh());
    }

    private function stockerLogo(?TemporaryUploadedFile $fichier): ?string
    {
        if ($fichier === null) {
            return null;
        }

        $nom = "saison-{$this->saison->id}-logo.".$fichier->getClientOriginalExtension();

        return $fichier->storeAs('visuels', $nom, 'data');
    }
}
