@props(['statut', 'titre' => null])
@php
    $classe = match ($statut) {
        \App\Enums\StatutAdhesion::Envoye => 'vert',
        \App\Enums\StatutAdhesion::Echec => 'rouge',
        default => 'gris',
    };
@endphp
<span @class(["badge", $classe, "avec-info" => $titre]) @if ($titre) title="{{ $titre }}" @endif>{{ $statut->libelle() }}</span>
