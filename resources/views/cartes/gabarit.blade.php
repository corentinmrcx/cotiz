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
            position: relative;
            width: {{ $largeur }}px;
            height: {{ $hauteur }}px;
            overflow: hidden;
            background-color: #1a5632;
            background-size: 100% 100%;
            background-repeat: no-repeat;
            page-break-after: always;
            break-after: page;
        }

        .face:last-child { page-break-after: auto; break-after: auto; }

        .face > * { position: absolute; margin: 0; line-height: 1; }

        .tarifs { list-style: none; padding: 0; }

        {!! $coordonnees !!}
    </style>
</head>
<body>
    <section class="face recto" @if ($carte->fondRecto) style="background-image: url('{{ $carte->fondRecto }}')" @endif>
        <p class="saison">{{ $carte->saison }}</p>
    </section>

    <section class="face verso" @if ($carte->fondVerso) style="background-image: url('{{ $carte->fondVerso }}')" @endif>
        <p class="nom">{{ $carte->nomComplet }}</p>
        <p class="nb-adultes">{{ $carte->nbAdultes }}</p>
        <p class="nb-enfants">{{ $carte->nbEnfants }}</p>
        <p class="cotisation">{{ $carte->cotisation }}</p>
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
