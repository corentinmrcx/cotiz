@props(['statut'])
@php
    $classe = match ($statut) {
        \App\Enums\StatutAdhesion::Envoye => 'vert',
        \App\Enums\StatutAdhesion::Echec => 'rouge',
        default => 'gris',
    };
@endphp
<span class="badge {{ $classe }}">{{ $statut->libelle() }}</span>
