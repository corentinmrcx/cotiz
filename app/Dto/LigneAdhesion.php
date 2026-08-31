<?php

namespace App\Dto;

use Carbon\CarbonImmutable;

final readonly class LigneAdhesion
{
    /** @param string[] $emails */
    public function __construct(
        public int $numeroLigne,
        public string $nom,
        public ?string $prenom,
        public array $emails,
        public int $nbAdultes,
        public int $nbEnfantsFamille,
        public int $nbEnfantsSeuls,
        public ?float $montantEncaisse,
        public ?string $modeReglement,
        public ?CarbonImmutable $dateReglement,
    ) {}

    public function nomComplet(): string
    {
        return trim(mb_strtoupper($this->nom).' '.($this->prenom ?? ''));
    }

    public function effectifTotal(): int
    {
        return $this->nbAdultes + $this->nbEnfantsFamille + $this->nbEnfantsSeuls;
    }
}
