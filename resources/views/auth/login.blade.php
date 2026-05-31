<x-layouts.app title="Connexion">
    <main class="auth-shell">
        <section class="panel" aria-labelledby="login-title">
            <h1 id="login-title">Connexion</h1>
            <p>Acces prive a la memoire projet.</p>

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="field">
                    <label for="email">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        autofocus
                    >
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Mot de passe</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                    >
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <label class="check">
                    <input name="remember" type="checkbox" value="1">
                    Se souvenir de moi
                </label>

                <button type="submit">Se connecter</button>
            </form>
        </section>
    </main>
</x-layouts.app>
