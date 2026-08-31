<?php

namespace App\Dto;

final readonly class DonneesCarte
{
    public function __construct(
        public string $saison,
        public string $nomComplet,
        public int $nbAdultes,
        public int $nbEnfants,
        public string $cotisation,
        public string $tarifAdulte,
        public string $tarifEnfantFamille,
        public string $tarifEnfantSeul,
        public string $assoNom,
        public string $assoEmail,
        public string $assoAdresse,
        public ?string $fondRecto,
        public ?string $fondVerso,
    ) {}
}
