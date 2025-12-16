<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-neutral-primary-soft text-body">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:font-semibold focus:text-gray-900 focus:ring-2 focus:ring-emerald-400 dark:focus:bg-neutral-900 dark:focus:text-gray-50">
            Saltar al contenido principal
        </a>
        <x-navigation.flowbite />

        <main id="main-content" tabindex="-1" class="pt-28">
            <div class="mx-auto w-full max-w-6xl px-6">
                <div class="mb-4">
                    <x-breadcrumbs />
                </div>
                {{ $slot }}
            </div>
        </main>

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

        @stack('scripts')
    </body>
</html>
