@php
    $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $input = 'mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
    $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700';
    $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
@endphp

<x-layouts.app :title="'Precios - ' . $product->name">
    <div class="mx-auto w-full max-w-7xl space-y-6">
        {{-- Header --}}
        <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 p-8 shadow-lg dark:from-emerald-600 dark:to-teal-700">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between text-white">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <a href="{{ route('food.products.index') }}" class="text-white/80 hover:text-white">
                            ← Productos
                        </a>
                        <span class="text-white/50">/</span>
                        <span class="text-sm">Gestión de Precios</span>
                    </div>
                    <h1 class="text-3xl font-bold">{{ $product->name }}</h1>
                    @if($product->brand)
                        <p class="text-sm text-white/80">{{ $product->brand }}</p>
                    @endif
                </div>

                {{-- Precio Actual vs Anterior --}}
                <div class="flex gap-4">
                    <div class="rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                        <p class="text-xs text-white/80">Precio Actual</p>
                        @if($currentPrice)
                            <p class="text-2xl font-bold">${{ number_format($currentPrice->price_per_base, 0, ',', '.') }}</p>
                            @if($currentPrice->vendor)
                                <p class="text-xs text-white/70">{{ $currentPrice->vendor }}</p>
                            @endif
                        @else
                            <p class="text-2xl font-bold text-white/50">—</p>
                        @endif
                    </div>

                    @if($previousPrice)
                        <div class="rounded-xl bg-white/5 px-4 py-3 ring-1 ring-white/10">
                            <p class="text-xs text-white/60">Anterior</p>
                            <p class="text-xl font-bold text-white/80">${{ number_format($previousPrice->price_per_base, 0, ',', '.') }}</p>
                            @php
                                $change = (($currentPrice->price_per_base - $previousPrice->price_per_base) / $previousPrice->price_per_base) * 100;
                            @endphp
                            @if(abs($change) >= 1)
                                <p class="text-xs {{ $change > 0 ? 'text-rose-300' : 'text-emerald-300' }}">
                                    {{ $change > 0 ? '↑' : '↓' }} {{ abs(number_format($change, 1)) }}%
                                </p>
                            @else
                                <p class="text-xs text-white/50">Sin cambios</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Gráfica de Histórico --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-md dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Histórico de Precios</h2>
                        <div class="flex gap-2">
                            <button onclick="loadChart(30)" class="text-xs px-3 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700">
                                30d
                            </button>
                            <button onclick="loadChart(90)" class="text-xs px-3 py-1 rounded-lg bg-emerald-100 hover:bg-emerald-200 dark:bg-emerald-900/30">
                                90d
                            </button>
                            <button onclick="loadChart(365)" class="text-xs px-3 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700">
                                1 año
                            </button>
                        </div>
                    </div>
                    <canvas id="priceChart" class="w-full" height="300"></canvas>
                </div>

                {{-- Proyección --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-md dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Proyección (30 días)</h2>
                        <button onclick="loadForecast()" class="{{ $btnSecondary }} text-xs">
                            🔮 Actualizar
                        </button>
                    </div>
                    <div id="forecast-container">
                        <canvas id="forecastChart" class="w-full" height="250"></canvas>
                    </div>
                    <div id="forecast-info" class="mt-4 text-sm text-gray-600 dark:text-gray-400"></div>
                </div>

                {{-- Tabla de Histórico --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-md dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Registros</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr class="text-left text-xs uppercase text-gray-500">
                                    <th class="px-3 py-2">Fecha</th>
                                    <th class="px-3 py-2">Precio</th>
                                    <th class="px-3 py-2">Vendor</th>
                                    <th class="px-3 py-2">Cambio</th>
                                    <th class="px-3 py-2">Fuente</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($priceHistory as $index => $price)
                                    @php
                                        $prev = $priceHistory[$index + 1] ?? null;
                                        $change = $prev ? (($price->price_per_base - $prev->price_per_base) / $prev->price_per_base) * 100 : null;
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-3 py-3 text-gray-900 dark:text-gray-100">
                                            {{ $price->captured_on->format('d/m/Y') }}
                                        </td>
                                        <td class="px-3 py-3 font-semibold text-gray-900 dark:text-gray-100">${{ number_format($price->price_per_base, 0, ',', '.') }}</td>
                                        <td class="px-3 py-3 text-gray-600 dark:text-gray-300">{{ $price->vendor ?? '—' }}</td>
                                        <td class="px-3 py-3">
                                            @if($change !== null && abs($change) >= 1)
                                                <span class="text-xs font-semibold {{ $change > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                                    {{ $change > 0 ? '↑' : '↓' }} {{ abs(number_format($change, 1)) }}%
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-xs text-gray-500">{{ $price->source ?? 'manual' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-8 text-center text-gray-500">
                                            No hay registros de precios. Agrega el primero →
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Agregar Precio --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-md dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Registrar Precio</h3>
                    <form id="price-form" class="space-y-3">
                        <div>
                            <label class="{{ $label }}">Precio *</label>
                            <input type="number" step="0.01" name="price" required class="{{ $input }}" placeholder="0.00">
                        </div>
                        <div>
                            <label class="{{ $label }}">Tienda/Vendor</label>
                            <input type="text" name="vendor" class="{{ $input }}" placeholder="Ej: Walmart, Soriana">
                        </div>
                        <div>
                            <label class="{{ $label }}">Fecha</label>
                            <input type="date" name="captured_on" value="{{ now()->format('Y-m-d') }}" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">Nota</label>
                            <textarea name="note" rows="2" class="{{ $input }}" placeholder="Opcional..."></textarea>
                        </div>
                        <button type="submit" class="{{ $btnPrimary }} w-full">
                            + Agregar Precio
                        </button>
                    </form>
                </div>

                {{-- Estadísticas --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-md dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Estadísticas (1 año)</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Promedio:</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">${{ number_format($stats['avg'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Mínimo:</span>
                            <span class="text-sm font-semibold text-emerald-600">${{ number_format($stats['min'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Máximo:</span>
                            <span class="text-sm font-semibold text-rose-600">${{ number_format($stats['max'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Mediana:</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">${{ number_format($stats['median'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Volatilidad:</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">±${{ number_format($stats['volatility'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Registros:</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $stats['total_records'] }}</span>
                        </div>
                    </div>
                </div>

                {{-- Tendencia --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-md dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Tendencia (30 días)</h3>
                    <div class="text-center">
                        @if($trend['direction'] === 'up')
                            <div class="text-rose-500 text-4xl mb-2">↑</div>
                            <p class="text-sm font-semibold text-rose-600">Subiendo</p>
                            <p class="text-xs text-gray-500 mt-1">+{{ number_format(abs($trend['change']), 1) }}%</p>
                        @elseif($trend['direction'] === 'down')
                            <div class="text-emerald-500 text-4xl mb-2">↓</div>
                            <p class="text-sm font-semibold text-emerald-600">Bajando</p>
                            <p class="text-xs text-gray-500 mt-1">{{ number_format($trend['change'], 1) }}%</p>
                        @else
                            <div class="text-blue-500 text-4xl mb-2">→</div>
                            <p class="text-sm font-semibold text-blue-600">Estable</p>
                            <p class="text-xs text-gray-500 mt-1">Sin cambios significativos</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const productId = {{ $product->id }};
        let priceChart = null;
        let forecastChart = null;

        // Cargar gráfica de histórico
        async function loadChart(days = 90) {
            try {
                const res = await fetch(`/food/products/${productId}/prices/chart?days=${days}`);
                const data = await res.json();

                if (priceChart) {
                    priceChart.destroy();
                }

                const ctx = document.getElementById('priceChart').getContext('2d');
                priceChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Precio',
                            data: data.data,
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.3,
                            fill: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '$' + new Intl.NumberFormat('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(context.parsed.y) + ' - ' + (data.vendors[context.dataIndex] || 'Sin vendor');
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + new Intl.NumberFormat('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
                                    }
                                }
                            }
                        }
                    }
                });
            } catch (err) {
                console.error(err);
            }
        }

        // Cargar proyección
        async function loadForecast() {
            try {
                const res = await fetch(`/food/products/${productId}/prices/forecast?days=30`);
                const data = await res.json();

                if (forecastChart) {
                    forecastChart.destroy();
                }

                const historicalLabels = data.historical.map(h => h.date);
                const historicalData = data.historical.map(h => h.price);
                const forecastLabels = data.forecast.data.map(f => f.date);
                const forecastData = data.forecast.data.map(f => f.price);

                const ctx = document.getElementById('forecastChart').getContext('2d');
                forecastChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: [...historicalLabels, ...forecastLabels],
                        datasets: [
                            {
                                label: 'Histórico',
                                data: [...historicalData, ...Array(forecastData.length).fill(null)],
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.3,
                            },
                            {
                                label: 'Proyección',
                                data: [...Array(historicalData.length).fill(null), ...forecastData],
                                borderColor: 'rgb(249, 115, 22)',
                                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                                borderDash: [5, 5],
                                tension: 0.3,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: false,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + new Intl.NumberFormat('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value);
                                    }
                                }
                            }
                        }
                    }
                });

                // Mostrar info de tendencia
                const trendIcon = data.trend === 'increasing' ? '📈' : (data.trend === 'decreasing' ? '📉' : '➡️');
                const trendText = data.trend === 'increasing' ? 'aumentar' : (data.trend === 'decreasing' ? 'disminuir' : 'mantenerse estable');
                document.getElementById('forecast-info').innerHTML = `
                    <p class="text-center">${trendIcon} Se proyecta que el precio va a <strong>${trendText}</strong> en los próximos 30 días</p>
                `;
            } catch (err) {
                console.error(err);
                document.getElementById('forecast-info').innerHTML = `
                    <p class="text-center text-rose-600">⚠️ No hay suficientes datos para generar proyección (mínimo 3 registros)</p>
                `;
            }
        }

        // Registrar nuevo precio
        document.getElementById('price-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);

            try {
                const res = await fetch(`/food/products/${productId}/prices`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify(data),
                });

                if (res.ok) {
                    alert('Precio registrado correctamente');
                    location.reload();
                } else {
                    alert('Error al registrar precio');
                }
            } catch (err) {
                console.error(err);
                alert('Error al registrar precio');
            }
        });

        // Cargar datos iniciales
        loadChart(90);
        loadForecast();
    </script>
    @endpush
</x-layouts.app>
