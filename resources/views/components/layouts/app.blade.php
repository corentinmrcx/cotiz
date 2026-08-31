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
        @unless (request()->routeIs('login'))
            <nav class="navigation">
                <a href="{{ route('import') }}" @class(['actif' => request()->routeIs('import')])>Import</a>
                <a href="{{ route('adhesions') }}" @class(['actif' => request()->routeIs('adhesions*')])>Adhésions</a>
                <a href="{{ route('historique') }}" @class(['actif' => request()->routeIs('historique*')])>Historique</a>
                <a href="{{ route('reglages') }}" @class(['actif' => request()->routeIs('reglages')])>Réglages</a>
            </nav>
            @auth
                <form method="POST" action="{{ route('logout') }}" class="deconnexion">
                    @csrf
                    <button type="submit">Déconnexion</button>
                </form>
            @endauth
        @endunless
    </header>
    <main class="contenu">
        {{ $slot }}
    </main>
    <footer class="pied">
        Propulsé par <a href="https://codepp.fr" target="_blank" rel="noopener">Codepp</a>
    </footer>
    @livewireScripts
</body>
</html>
