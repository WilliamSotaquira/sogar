<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <div class="space-y-1">
            <p class="text-xs font-semibold uppercase tracking-wide text-body">Crear cuenta</p>
            <h1 class="text-2xl font-semibold text-heading">Regístrate</h1>
            <p class="text-sm text-body">Ingresa tus datos para comenzar.</p>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
            @csrf

            <div class="space-y-1">
                <label for="name" class="text-sm font-medium text-heading">Nombre completo</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Nombre y apellidos"
                    class="h-12 w-full rounded-xl border border-default bg-white px-3 text-sm text-heading shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                >
                @error('name') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1">
                <label for="email" class="text-sm font-medium text-heading">Correo electrónico</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    placeholder="nombre@ejemplo.com"
                    class="h-12 w-full rounded-xl border border-default bg-white px-3 text-sm text-heading shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                >
                @error('email') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1">
                <label for="password" class="text-sm font-medium text-heading">Contraseña</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Contraseña"
                    class="h-12 w-full rounded-xl border border-default bg-white px-3 text-sm text-heading shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                >
                @error('password') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1">
                <label for="password_confirmation" class="text-sm font-medium text-heading">Confirmar contraseña</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    placeholder="Confirmar contraseña"
                    class="h-12 w-full rounded-xl border border-default bg-white px-3 text-sm text-heading shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                >
            </div>

            <button
                type="submit"
                class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-sky-700 px-4 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-sky-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2"
            >
                Crear cuenta
            </button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-body">
            <span>¿Ya tienes cuenta?</span>
            <a href="{{ route('login') }}" wire:navigate class="font-semibold text-sky-700 hover:text-sky-800 dark:text-sky-300">Entrar</a>
        </div>
    </div>
</x-layouts.auth>
