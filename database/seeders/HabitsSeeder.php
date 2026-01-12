<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityGoal;
use App\Models\ActivityLog;
use App\Models\Budget;
use App\Models\Category;
use App\Models\ShoppingList;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HabitsSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production') && !env('SOGAR_SEED_ALLOW_PROD', false)) {
            $this->command?->warn('⛔ HabitsSeeder: se omite en producción. Define SOGAR_SEED_ALLOW_PROD=true si realmente deseas ejecutarlo.');
            return;
        }

        $williamEmail = env('SOGAR_SEED_WILLIAM_EMAIL', 'william@example.com');
        $jazminEmail = env('SOGAR_SEED_JAZMIN_EMAIL', 'jazmin@example.com');
        $santiagoEmail = env('SOGAR_SEED_SANTIAGO_EMAIL', 'santiago@example.com');

        $users = User::query()
            ->whereIn('email', [strtolower(trim($williamEmail)), strtolower(trim($jazminEmail)), strtolower(trim($santiagoEmail))])
            ->get()
            ->keyBy('email');

        /** @var User|null $william */
        $william = $users[strtolower(trim($williamEmail))] ?? null;
        /** @var User|null $jazmin */
        $jazmin = $users[strtolower(trim($jazminEmail))] ?? null;
        /** @var User|null $santiago */
        $santiago = $users[strtolower(trim($santiagoEmail))] ?? null;

        if (!$william && !$jazmin && !$santiago) {
            $this->command?->warn('ℹ️ HabitsSeeder: no se encontraron usuarios seed; ejecuta primero UserSeeder.');
            return;
        }

        $today = Carbon::today();

        // 1) Actividades personales (William)
        if ($william) {
            $this->seedPackForUser(
                user: $william,
                familyGroupId: null,
                today: $today,
                pack: [
                    ['title' => 'Leer 20 minutos', 'kind' => 'habit', 'cadence' => 'daily', 'target' => 1, 'unit' => 'check'],
                    ['title' => 'Ejercicio', 'kind' => 'habit', 'cadence' => 'weekly', 'target' => 3, 'unit' => 'sesiones'],
                    ['title' => 'Plan semanal', 'kind' => 'task', 'cadence' => 'weekly', 'target' => 1, 'unit' => 'check'],
                    ['title' => 'Revisar gastos', 'kind' => 'habit', 'cadence' => 'monthly', 'target' => 1, 'unit' => 'check'],
                ]
            );
        }

        // 2) Actividades compartidas por núcleo (si existe family_group activo)
        foreach ([$william, $jazmin, $santiago] as $user) {
            if (!$user || !$user->active_family_group_id) {
                continue;
            }

            $this->seedPackForUser(
                user: $user,
                familyGroupId: (int) $user->active_family_group_id,
                today: $today,
                pack: [
                    ['title' => 'Sacar la basura', 'kind' => 'habit', 'cadence' => 'weekly', 'target' => 1, 'unit' => 'check'],
                    ['title' => 'Orden y limpieza 15 min', 'kind' => 'habit', 'cadence' => 'daily', 'target' => 1, 'unit' => 'check'],
                ]
            );
        }

        // 2.5) Actividades interoperables (subject polimórfico)
        foreach ([$william, $jazmin, $santiago] as $user) {
            if (!$user) {
                continue;
            }

            $this->seedInteroperableActivities($user, $today);
        }

        // 3) Objetivo avanzado ejemplo (ActivityGoal) para una actividad diaria
        if ($william) {
            $activity = Activity::query()
                ->where('user_id', $william->id)
                ->whereNull('family_group_id')
                ->where('title', 'Leer 20 minutos')
                ->first();

            if ($activity) {
                ActivityGoal::updateOrCreate(
                    ['activity_id' => $activity->id, 'goal_type' => 'streak'],
                    [
                        'user_id' => $william->id,
                        'family_group_id' => null,
                        'target_value' => 14,
                        'period' => null,
                        'starts_on' => $today->copy()->subDays(30),
                        'ends_on' => null,
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command?->info('✅ HabitsSeeder: actividades y registros creados/actualizados.');
    }

    private function seedInteroperableActivities(User $user, Carbon $today): void
    {
        $wallet = Wallet::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (!$wallet) {
            $wallet = Wallet::query()
                ->where('user_id', $user->id)
                ->first();
        }

        if ($wallet) {
            $activity = Activity::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'family_group_id' => null,
                    'subject_type' => Wallet::class,
                    'subject_id' => $wallet->id,
                ],
                [
                    'title' => 'Registrar gastos (Billetera)',
                    'description' => 'Check-in rápido para mantener tu billetera al día.',
                    'kind' => 'habit',
                    'cadence' => 'daily',
                    'target_count' => 1,
                    'unit' => 'check',
                    'start_on' => $today->copy()->subDays(30),
                    'end_on' => null,
                    'due_on' => null,
                    'is_active' => true,
                    'meta' => null,
                ]
            );

            $this->seedLogsForActivity($activity, $user, $today);
        }

        $budget = Budget::query()
            ->where('user_id', $user->id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByDesc('id')
            ->first();

        if (!$budget) {
            $category = Category::query()
                ->where('user_id', $user->id)
                ->where('type', 'expense')
                ->where('name', 'Gastos del hogar')
                ->first();

            if (!$category) {
                $category = Category::create([
                    'user_id' => $user->id,
                    'name' => 'Gastos del hogar',
                    'type' => 'expense',
                    'description' => 'Categoría demo para presupuesto.',
                    'color' => '#6366F1',
                    'is_active' => true,
                ]);
            }

            $budget = Budget::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'month' => (int) $today->month,
                    'year' => (int) $today->year,
                ],
                [
                    'amount' => 500,
                    'is_flexible' => true,
                    'sync_to_calendar' => false,
                ]
            );
        }

        if ($budget) {
            $activity = Activity::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'family_group_id' => null,
                    'subject_type' => Budget::class,
                    'subject_id' => $budget->id,
                ],
                [
                    'title' => 'Revisar presupuesto (Finanzas)',
                    'description' => 'Revisar si vas dentro del presupuesto del mes.',
                    'kind' => 'habit',
                    'cadence' => 'monthly',
                    'target_count' => 1,
                    'unit' => 'check',
                    'start_on' => $today->copy()->subDays(90),
                    'end_on' => null,
                    'due_on' => null,
                    'is_active' => true,
                    'meta' => null,
                ]
            );

            $this->seedLogsForActivity($activity, $user, $today);
        }

        $list = ShoppingList::query()
            ->where('user_id', $user->id)
            ->orderByDesc('generated_at')
            ->first();

        if (!$list) {
            $list = ShoppingList::create([
                'user_id' => $user->id,
                'family_group_id' => $user->active_family_group_id,
                'name' => 'Lista demo - ' . now()->locale('es')->translatedFormat('j M'),
                'list_type' => 'general',
                'status' => 'active',
                'generated_at' => now(),
            ]);
        }

        $activity = Activity::updateOrCreate(
            [
                'user_id' => $user->id,
                'family_group_id' => $list->family_group_id,
                'subject_type' => ShoppingList::class,
                'subject_id' => $list->id,
            ],
            [
                'title' => 'Actualizar lista de compra',
                'description' => 'Revisar y ajustar items antes de comprar.',
                'kind' => 'task',
                'cadence' => 'weekly',
                'target_count' => 1,
                'unit' => 'check',
                'start_on' => $today->copy()->subDays(30),
                'end_on' => null,
                'due_on' => null,
                'is_active' => true,
                'meta' => null,
            ]
        );

        $this->seedLogsForActivity($activity, $user, $today);
    }

    /**
     * @param array<int, array{title:string,kind:string,cadence:string,target:int,unit:?string}> $pack
     */
    private function seedPackForUser(User $user, ?int $familyGroupId, Carbon $today, array $pack): void
    {
        foreach ($pack as $row) {
            $activity = Activity::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'family_group_id' => $familyGroupId,
                    'title' => $row['title'],
                ],
                [
                    'description' => null,
                    'kind' => $row['kind'],
                    'cadence' => $row['cadence'],
                    'target_count' => $row['target'],
                    'unit' => $row['unit'],
                    'start_on' => $today->copy()->subDays(30),
                    'end_on' => null,
                    'due_on' => $row['cadence'] === 'once' ? $today->toDateString() : null,
                    'is_active' => true,
                    'meta' => null,
                ]
            );

            $this->seedLogsForActivity($activity, $user, $today);
        }
    }

    private function seedLogsForActivity(Activity $activity, User $user, Carbon $today): void
    {
        // Historial para que el dashboard se vea con datos reales.
        switch ((string) $activity->cadence) {
            case 'daily':
                // Crea una racha de 7 días y algunos días sueltos antes.
                for ($d = 1; $d <= 21; $d++) {
                    $day = $today->copy()->subDays($d);

                    $qty = 0;
                    if ($d <= 7) {
                        $qty = (int) $activity->target_count; // racha reciente
                    } elseif ($d % 3 === 0) {
                        $qty = (int) $activity->target_count;
                    }

                    if ($qty > 0) {
                        ActivityLog::updateOrCreate(
                            [
                                'activity_id' => $activity->id,
                                'user_id' => $user->id,
                                'occurred_on' => $day->toDateString(),
                            ],
                            [
                                'occurred_at' => $day->copy()->setTime(20, 0),
                                'qty' => $qty,
                                'note' => null,
                                'meta' => null,
                            ]
                        );
                    }
                }
                break;

            case 'weekly':
                // 6 semanas: 1-2 cumplimientos por semana.
                for ($w = 1; $w <= 6; $w++) {
                    $base = $today->copy()->subWeeks($w)->startOfWeek(Carbon::MONDAY);
                    $hits = min(2, max(1, (int) floor(((int) $activity->target_count) / 2) ?: 1));

                    for ($i = 0; $i < $hits; $i++) {
                        $day = $base->copy()->addDays(1 + ($i * 2));
                        ActivityLog::updateOrCreate(
                            [
                                'activity_id' => $activity->id,
                                'user_id' => $user->id,
                                'occurred_on' => $day->toDateString(),
                            ],
                            [
                                'occurred_at' => $day->copy()->setTime(19, 0),
                                'qty' => 1,
                                'note' => null,
                                'meta' => null,
                            ]
                        );
                    }
                }
                break;

            case 'monthly':
                // 2 meses: una ejecución por mes.
                for ($m = 1; $m <= 2; $m++) {
                    $day = $today->copy()->subMonthsNoOverflow($m)->startOfMonth()->addDays(10);
                    ActivityLog::updateOrCreate(
                        [
                            'activity_id' => $activity->id,
                            'user_id' => $user->id,
                            'occurred_on' => $day->toDateString(),
                        ],
                        [
                            'occurred_at' => $day->copy()->setTime(18, 30),
                            'qty' => 1,
                            'note' => null,
                            'meta' => null,
                        ]
                    );
                }
                break;

            case 'once':
            default:
                break;
        }
    }
}
