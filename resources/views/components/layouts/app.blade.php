<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titre ?? '' }} — CoTiz</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @livewireStyles
</head>
<body>
    <header class="entete">
        <a class="marque" href="{{ route('accueil') }}">CoTiz</a>
        <nav class="navigation">
            <a href="{{ route('reglages') }}" @class(['actif' => request()->routeIs('reglages')])>Réglages</a>
        </nav>
    </header>
    <main class="contenu">
        {{ $slot }}
    </main>
    @livewireScripts
</body>
</html>
