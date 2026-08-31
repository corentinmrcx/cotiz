<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titre ?? '' }} — CoTiz</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="entete">
        <a class="marque" href="{{ route('accueil') }}">CoTiz</a>
        <nav class="navigation">
            <a href="{{ route('accueil') }}">Accueil</a>
        </nav>
    </header>
    <main class="contenu">
        {{ $slot }}
    </main>
</body>
</html>
