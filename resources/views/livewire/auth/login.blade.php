<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <div class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-body">Bienvenido</p>
            <h1 class="text-2xl font-semibold text-heading">Inicia sesión</h1>
            <p class="text-sm text-body">Ingresa tu correo y contraseña para continuar.</p>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            <div class="space-y-1">
                <label for="email" class="text-sm font-medium text-heading">Correo electrónico</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="nombre@ejemplo.com"
                    class="h-12 w-full rounded-xl border border-default bg-white px-3 text-sm text-heading shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                >
                @error('email') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1">
                <div class="flex items-center justify-between">
                    <label for="password" class="text-sm font-medium text-heading">Contraseña</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" wire:navigate class="text-xs font-semibold text-sky-600 hover:text-sky-700">¿Olvidaste tu contraseña?</a>
                    @endif
                </div>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Contraseña"
                    class="h-12 w-full rounded-xl border border-default bg-white px-3 text-sm text-heading shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                >
                @error('password') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-heading dark:text-white">
                <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="rounded border-default text-sky-600 focus:ring-sky-500 dark:border-neutral-700 dark:bg-neutral-800">
                Recordarme
            </label>

            <button
                type="submit"
                class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-sky-700 px-4 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-sky-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
                data-test="login-button"
            >
                Entrar
            </button>
        </form>

        @if (Route::has('register'))
            <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-body">
                <span>¿No tienes cuenta?</span>
                <a href="{{ route('register') }}" wire:navigate class="font-semibold text-sky-700 hover:text-sky-800 dark:text-sky-300">Crear cuenta</a>
            </div>
        @endif
    </div>
</x-layouts.auth>
