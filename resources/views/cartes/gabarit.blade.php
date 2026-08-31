<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Carte d'adhérent {{ $carte->saison }} — {{ $carte->nomComplet }}</title>
    <style>
        @font-face {
            font-family: 'Montserrat';
            font-weight: 100 900;
            src: url('{{ $police }}') format('truetype');
        }

        @page { size: {{ $largeur }}px {{ $hauteur }}px; margin: 0; }

        html, body { margin: 0; padding: 0; background: #fff; }

        body { font-family: 'Montserrat', sans-serif; -webkit-font-smoothing: antialiased; }

        .face {
            --h: {{ $hauteur / 100 }}px;
            --couleur: {{ $carte->couleur }};
            position: relative;
            width: {{ $largeur }}px;
            height: {{ $hauteur }}px;
            overflow: hidden;
            background: var(--couleur);
            color: #fff;
            page-break-after: always;
            break-after: page;
        }

        .face:last-child { page-break-after: auto; break-after: auto; }

        .face > * { position: absolute; margin: 0; line-height: 1; white-space: nowrap; }

        .tarifs { list-style: none; padding: 0; }

        {!! $coordonnees !!}
    </style>
</head>
<body>
    <section class="face recto">
        @if ($carte->logo)
            <img class="logo" src="{{ $carte->logo }}" alt="">
        @endif
        <p class="titre-ligne-1">CARTE</p>
        <p class="titre-ligne-2">D’ADHÉRENT</p>
        <p class="saison">{{ $carte->saison }}</p>
        <p class="asso-nom-recto">{{ mb_strtoupper($carte->assoNom) }}</p>
    </section>

    <section class="face verso">
        <div class="bandeau"></div>
        <p class="nom">{{ $carte->nomComplet }}</p>
        <p class="libelle-adultes">NB ADULTES :</p>
        <p class="libelle-enfants">NB ENFANTS :</p>
        <p class="libelle-cotisation">COTISATION :</p>
        <p class="cadre nb-adultes">{{ $carte->nbAdultes }}</p>
        <p class="cadre nb-enfants">{{ $carte->nbEnfants }}</p>
        <p class="cadre cotisation">{{ $carte->cotisation }}</p>
        <p class="euro">€</p>
        <div class="trait"></div>
        <p class="asso-nom">{{ $carte->assoNom }}</p>
        <p class="asso-email">{{ $carte->assoEmail }}</p>
        <p class="asso-adresse">{{ $carte->assoAdresse }}</p>
        <ul class="tarifs">
            <li><span>Adulte (+18 ans)</span><span>{{ $carte->tarifAdulte }}€</span></li>
            <li><span>Enfant (-18 ans en famille)</span><span>{{ $carte->tarifEnfantFamille }}€/enfant</span></li>
            <li><span>Enfant seul (-18 ans)</span><span>{{ $carte->tarifEnfantSeul }}€</span></li>
        </ul>
    </section>
</body>
</html>
