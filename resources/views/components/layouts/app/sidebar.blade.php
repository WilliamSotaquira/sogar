<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-neutral-primary-soft text-body">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:font-semibold focus:text-gray-900 focus:ring-2 focus:ring-emerald-400 dark:focus:bg-neutral-900 dark:focus:text-gray-50">
            Saltar al contenido principal
        </a>

        <div class="flex min-h-screen flex-col lg:flex-row">
            <div id="app-sidebar-overlay" class="fixed inset-0 z-40 hidden bg-neutral-950/50 lg:hidden" aria-hidden="true"></div>

            <aside
                id="app-sidebar"
                class="fixed inset-y-0 left-0 z-50 w-full -translate-x-full border-r border-light bg-neutral-primary text-body shadow-xl transition-transform duration-150 ease-out lg:static lg:z-auto lg:h-auto lg:w-72 lg:translate-x-0 lg:bg-neutral-primary lg:shadow-none"
                aria-label="Barra lateral"
            >
                <div class="flex h-full flex-col lg:sticky lg:top-0 lg:h-screen">
                    <div class="flex items-center justify-between gap-2 p-4 lg:hidden">
                        <a href="{{ route('dashboard') }}" wire:navigate class="group flex items-center gap-2.5" aria-label="{{ config('app.name') }}">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-neutral-primary-soft shadow-sm ring-1 ring-light transition duration-150 motion-reduce:transition-none group-hover:ring-fg-brand">
                                <x-app-logo-icon class="h-6 w-6" />
                            </span>
                            <span class="text-sm font-semibold text-heading transition duration-150 motion-reduce:transition-none group-hover:text-fg-brand">
                                {{ config('app.name') }}
                            </span>
                        </a>
                        <div class="flex items-center gap-2">
                            <button
                                data-theme-toggle
                                type="button"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200/70 text-gray-700 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-400 dark:border-neutral-700 dark:text-gray-200 dark:hover:bg-emerald-900/20"
                                aria-label="Alternar tema"
                            >
                                <svg data-theme-icon="dark" class="hidden h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>
                                <svg data-theme-icon="light" class="hidden h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"/></svg>
                            </button>
                            <button
                                id="app-sidebar-close"
                                type="button"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200/70 text-gray-700 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-400 dark:border-neutral-700 dark:text-gray-200 dark:hover:bg-emerald-900/20"
                                aria-label="Cerrar menú"
                            >
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="min-h-0 flex-1 overflow-hidden">
                        <x-navigation.sidebar />
                    </div>
                </div>
            </aside>

            <main id="main-content" tabindex="-1" class="flex-1 py-6 lg:py-10">
                <div class="mx-auto w-full max-w-6xl px-4 sm:px-6">
                    <div class="mb-4">
                        <div class="flex items-center justify-between gap-3 lg:hidden">
                            <a
                                href="{{ route('dashboard') }}"
                                wire:navigate
                                class="group flex flex-shrink-0 items-center"
                                aria-label="{{ config('app.name') }}"
                            >
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-neutral-primary-soft shadow-sm ring-1 ring-light transition duration-150 motion-reduce:transition-none group-hover:ring-fg-brand">
                                    <x-app-logo-icon class="h-6 w-6" />
                                </span>
                            </a>

                            <button
                                id="app-sidebar-open"
                                type="button"
                                class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg border border-gray-200/70 text-gray-700 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-400 dark:border-neutral-700 dark:text-gray-200 dark:hover:bg-emerald-900/20"
                                aria-label="Abrir menú"
                                aria-controls="app-sidebar"
                                aria-expanded="false"
                            >
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </div>

                        <div class="mt-3 lg:mt-0">
                            <x-breadcrumbs />
                        </div>
                    </div>
                    {{ $slot }}
                </div>
            </main>
        </div>

        <script>
            (() => {
                const __armed = new Set();
                const __resetByWrap = new WeakMap();

                const initInlineConfirm = (root = document) => {
                    root.querySelectorAll('[data-inline-confirm]').forEach((wrap) => {
                        if (wrap.dataset.inlineConfirmInit === '1') return;
                        wrap.dataset.inlineConfirmInit = '1';

                        const armBtn = wrap.querySelector('[data-inline-confirm-arm]');
                        const confirmBtn = wrap.querySelector('[data-inline-confirm-confirm]');
                        const cancelBtn = wrap.querySelector('[data-inline-confirm-cancel]');
                        const statusEl = wrap.querySelector('[data-inline-confirm-status]');
                        const detailsEl = wrap.querySelector('[data-inline-confirm-details]');
                        const timeoutMs = Number(wrap.dataset.inlineConfirmTimeout || 8000);

                        if (!armBtn || !confirmBtn || !cancelBtn) return;

                        let timer = null;

                        const setStatus = (text) => {
                            if (!statusEl) return;
                            statusEl.textContent = text || '';
                        };

                        const reset = () => {
                            timer && clearTimeout(timer);
                            timer = null;
                            __armed.delete(wrap);

                            armBtn.classList.remove('hidden');
                            confirmBtn.classList.add('hidden');
                            cancelBtn.classList.add('hidden');
                            detailsEl && detailsEl.classList.add('hidden');
                            setStatus('');
                        };

                        const arm = () => {
                            timer && clearTimeout(timer);
                            __armed.add(wrap);

                            armBtn.classList.add('hidden');
                            confirmBtn.classList.remove('hidden');
                            cancelBtn.classList.remove('hidden');
                            detailsEl && detailsEl.classList.remove('hidden');
                            setStatus('Confirmación activada. Presiona confirmar o cancelar.');

                            queueMicrotask(() => confirmBtn.focus());
                            timer = setTimeout(reset, timeoutMs);
                        };

                        __resetByWrap.set(wrap, reset);

                        armBtn.addEventListener('click', arm);
                        armBtn.addEventListener('keydown', (e) => {
                            if (e.key === 'Enter' || e.key === ' ') {
                                e.preventDefault();
                                arm();
                            }
                        });
                        cancelBtn.addEventListener('click', () => {
                            reset();
                            queueMicrotask(() => armBtn.focus());
                        });
                    });
                };

                const boot = () => initInlineConfirm(document);

                document.addEventListener('DOMContentLoaded', boot);
                // SPA-like navigation support (Livewire/Turbo/Turbolinks)
                document.addEventListener('livewire:navigated', boot);
                document.addEventListener('turbo:load', boot);
                document.addEventListener('turbolinks:load', boot);
                document.addEventListener('click', (e) => {
                    if (__armed.size === 0) return;
                    for (const wrap of Array.from(__armed)) {
                        if (wrap.contains(e.target)) continue;
                        __resetByWrap.get(wrap)?.();
                    }
                });
                window.SogarUI = window.SogarUI || {};
                window.SogarUI.initInlineConfirm = initInlineConfirm;
            })();
        </script>

        <script>
            (() => {
                const sidebar = document.getElementById('app-sidebar');
                const overlay = document.getElementById('app-sidebar-overlay');
                const openBtn = document.getElementById('app-sidebar-open');
                const closeBtn = document.getElementById('app-sidebar-close');
                const main = document.getElementById('main-content');

                if (!sidebar || !overlay || !openBtn || !closeBtn) return;

                let lastActive = null;

                const setMainInert = (value) => {
                    if (!main) return;
                    if ('inert' in main) {
                        main.inert = value;
                    }
                    main.setAttribute('aria-hidden', value ? 'true' : 'false');
                };

                const openSidebar = () => {
                    lastActive = document.activeElement;
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                    openBtn.setAttribute('aria-expanded', 'true');
                    document.body.classList.add('overflow-hidden');
                    setMainInert(true);
                    queueMicrotask(() => closeBtn.focus());
                };

                const closeSidebar = () => {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    openBtn.setAttribute('aria-expanded', 'false');
                    document.body.classList.remove('overflow-hidden');
                    setMainInert(false);
                    queueMicrotask(() => {
                        if (lastActive && typeof lastActive.focus === 'function') {
                            lastActive.focus();
                            return;
                        }
                        openBtn.focus();
                    });
                };

                const isOpen = () => !sidebar.classList.contains('-translate-x-full');

                openBtn.addEventListener('click', () => openSidebar());
                closeBtn.addEventListener('click', () => closeSidebar());
                overlay.addEventListener('click', () => closeSidebar());

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && isOpen()) {
                        e.preventDefault();
                        closeSidebar();
                    }
                });

                // Nota: no cerramos automáticamente al navegar desde el sidebar en móvil.

                // Si se cambia a desktop, asegura estado visible y sin overlay
                const mq = window.matchMedia ? window.matchMedia('(min-width: 1024px)') : null;
                const syncForViewport = () => {
                    if (!mq) return;
                    if (mq.matches) {
                        sidebar.classList.remove('-translate-x-full');
                        overlay.classList.add('hidden');
                        openBtn.setAttribute('aria-expanded', 'false');
                        document.body.classList.remove('overflow-hidden');
                        setMainInert(false);
                    } else {
                        // En móvil inicia oculto
                        sidebar.classList.add('-translate-x-full');
                        overlay.classList.add('hidden');
                        openBtn.setAttribute('aria-expanded', 'false');
                        document.body.classList.remove('overflow-hidden');
                        setMainInert(false);
                    }
                };
                mq?.addEventListener?.('change', syncForViewport);
                document.addEventListener('DOMContentLoaded', syncForViewport);
                document.addEventListener('livewire:navigated', syncForViewport);
                document.addEventListener('turbo:load', syncForViewport);
                document.addEventListener('turbolinks:load', syncForViewport);

                window.SogarUI = window.SogarUI || {};
                window.SogarUI.openSidebar = openSidebar;
                window.SogarUI.closeSidebar = closeSidebar;
            })();
        </script>

        @stack('scripts')
    </body>
</html>
