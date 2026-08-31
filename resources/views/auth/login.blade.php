<x-layouts.app titre="Connexion">
    <section class="carte connexion">
        <h1>Connexion</h1>
        <form method="POST" action="{{ route('login.connecter') }}" class="formulaire">
            @csrf
            <label>Adresse mail
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email') <span class="champ-erreur">{{ $message }}</span> @enderror
            </label>
            <label>Mot de passe
                <input type="password" name="password" required>
                @error('password') <span class="champ-erreur">{{ $message }}</span> @enderror
            </label>
            <div class="actions">
                <button type="submit" class="bouton">Se connecter</button>
            </div>
        </form>
    </section>
</x-layouts.app>
