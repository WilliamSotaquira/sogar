<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        @php
            $links = [
                ['route' => 'profile.edit', 'label' => __('Profile')],
                ['route' => 'user-password.edit', 'label' => __('Password')],
            ];

            if (Laravel\Fortify\Features::canManageTwoFactorAuthentication()) {
                $links[] = ['route' => 'two-factor.show', 'label' => __('Two-Factor Auth')];
            }

            $links[] = ['route' => 'appearance.edit', 'label' => __('Appearance')];
        @endphp

        <nav class="rounded-xl border border-default bg-white p-3 shadow-sm dark:bg-neutral-900">
            <ul class="space-y-1 text-sm font-medium">
                @foreach ($links as $link)
                    @php $active = request()->routeIs(Str::before($link['route'], '.') . '*'); @endphp
                    <li>
                        <a
                            href="{{ route($link['route']) }}"
                            wire:navigate
                            class="flex items-center justify-between rounded-lg px-3 py-2 transition hover:bg-neutral-100 dark:hover:bg-neutral-800 {{ $active ? 'bg-neutral-100 text-heading dark:bg-neutral-800' : 'text-body' }}"
                        >
                            <span>{{ $link['label'] }}</span>
                            @if ($active)
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>

    <div class="hidden h-px w-full bg-default md:hidden"></div>

    <div class="flex-1 self-stretch max-md:pt-6">
        @if(!empty($heading ?? ''))
            <h1 class="text-2xl font-semibold text-heading">{{ $heading }}</h1>
        @endif
        @if(!empty($subheading ?? ''))
            <p class="mt-1 text-body">{{ $subheading }}</p>
        @endif

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
