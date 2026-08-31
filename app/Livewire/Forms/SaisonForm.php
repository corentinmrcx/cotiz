<?php

namespace App\Livewire\Forms;

use App\Models\Saison;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class SaisonForm extends Form
{
    public ?Saison $saison = null;

    public string $libelle = '';

    public string $tarif_adulte = '';

    public string $tarif_enfant_famille = '';

    public string $tarif_enfant_seul = '';

    public ?TemporaryUploadedFile $visuel_recto = null;

    public ?TemporaryUploadedFile $visuel_verso = null;

    public function rules(): array
    {
        return [
            'libelle' => ['required', 'string', 'regex:/^\d{4}-\d{4}$/'],
            'tarif_adulte' => ['required', 'numeric', 'min:0'],
            'tarif_enfant_famille' => ['required', 'numeric', 'min:0'],
            'tarif_enfant_seul' => ['required', 'numeric', 'min:0'],
            'visuel_recto' => ['nullable', 'image', 'max:5120'],
            'visuel_verso' => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function validationAttributes(): array
    {
        return [
            'libelle' => 'libellé',
            'tarif_adulte' => 'tarif adulte',
            'tarif_enfant_famille' => 'tarif enfant en famille',
            'tarif_enfant_seul' => 'tarif enfant seul',
            'visuel_recto' => 'visuel recto',
            'visuel_verso' => 'visuel verso',
        ];
    }

    public function charger(Saison $saison): void
    {
        $this->saison = $saison;
        $this->libelle = $saison->libelle;
        $this->tarif_adulte = (string) $saison->tarif_adulte;
        $this->tarif_enfant_famille = (string) $saison->tarif_enfant_famille;
        $this->tarif_enfant_seul = (string) $saison->tarif_enfant_seul;
        $this->visuel_recto = null;
        $this->visuel_verso = null;
    }

    public function enregistrer(): void
    {
        $this->validate();

        $this->saison->update([
            'libelle' => $this->libelle,
            'tarif_adulte' => $this->tarif_adulte,
            'tarif_enfant_famille' => $this->tarif_enfant_famille,
            'tarif_enfant_seul' => $this->tarif_enfant_seul,
            'visuel_recto' => $this->stockerVisuel($this->visuel_recto, 'recto') ?? $this->saison->visuel_recto,
            'visuel_verso' => $this->stockerVisuel($this->visuel_verso, 'verso') ?? $this->saison->visuel_verso,
        ]);

        $this->charger($this->saison->fresh());
    }

    private function stockerVisuel(?TemporaryUploadedFile $fichier, string $face): ?string
    {
        if ($fichier === null) {
            return null;
        }

        $nom = "saison-{$this->saison->id}-{$face}.".$fichier->getClientOriginalExtension();

        return $fichier->storeAs('visuels', $nom, 'data');
    }
}
