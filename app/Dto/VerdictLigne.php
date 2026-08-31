<?php

namespace App\Dto;

use App\Enums\NiveauVerdict;

final readonly class VerdictLigne
{
    /** @param string[] $motifs */
    public function __construct(
        public LigneAdhesion $ligne,
        public NiveauVerdict $niveau,
        public array $motifs,
        public float $cotisationCalculee,
    ) {}

    public function estImportable(): bool
    {
        return $this->niveau !== NiveauVerdict::Rejet;
    }
}
