<x-layouts.guest title="Entrar">

<div class="guest-bg">
    <div class="login-card">

        <div class="login-logo text-center mb-8">
            <div class="login-logo-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                    <path d="M2 17l10 5 10-5"/>
                    <path d="M2 12l10 5 10-5"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white mb-1.5">Bem-vindo de volta</h1>
            <p class="text-sm text-slate-400">Lymity AI Agency Platform</p>
        </div>

        @if ($errors->any())
        <div class="alert alert-error mb-5 text-sm">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">E-mail</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input {{ $errors->has('email') ? 'border-red-500' : '' }}"
                    placeholder="seu@email.com"
                    value="{{ old('email') }}"
                    autofocus
                    autocomplete="email"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Senha</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                >
            </div>

            <div class="flex items-center gap-2 mb-6">
                <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded accent-blue-500">
                <label for="remember" class="text-sm text-slate-400 cursor-pointer">Lembrar acesso</label>
            </div>

            <button type="submit" class="btn btn-primary w-full justify-center py-3 text-base font-semibold rounded-xl">
                Entrar na plataforma
            </button>
        </form>


    </div>
</div>

</x-layouts.guest>
