<?php

namespace Database\Seeders;

use App\Models\Routine;
use App\Models\RoutineItem;
use App\Models\RoutineItemLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RoutinesSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production') && !env('SOGAR_SEED_ALLOW_PROD', false)) {
            $this->command?->warn('⛔ RoutinesSeeder: se omite en producción. Define SOGAR_SEED_ALLOW_PROD=true si realmente deseas ejecutarlo.');
            return;
        }

        $williamEmail = env('SOGAR_SEED_WILLIAM_EMAIL', 'william@example.com');

        /** @var User|null $user */
        $user = User::query()->where('email', strtolower(trim($williamEmail)))->first();
        if (!$user) {
            $this->command?->warn('ℹ️ RoutinesSeeder: no se encontró el usuario seed (William). Ejecuta primero UserSeeder.');
            return;
        }

        $today = Carbon::today();

        // Rutina: Día hábil (Mon..Fri)
        $weekdayMask = 31;
        $workday = Routine::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'family_group_id' => null,
                'name' => 'Día hábil',
            ],
            [
                'description' => 'Plantilla detallada de lunes a viernes (importada de tabla).',
                'is_active' => true,
            ]
        );

        // Reemplazar el contenido para que el seed sea idempotente y fiel a la tabla.
        RoutineItem::query()->where('routine_id', $workday->id)->delete();

        $this->seedItems($workday, $weekdayMask, [
            // Sueño (madrugada)
            ['00:00', '00:25', 'Dormir', 'Salud', null],
            ['00:30', '00:55', 'Dormir', 'Salud', null],
            ['01:00', '01:25', 'Dormir', 'Salud', null],
            ['01:30', '01:55', 'Dormir', 'Salud', null],
            ['02:00', '02:25', 'Dormir', 'Salud', null],
            ['02:30', '02:55', 'Dormir', 'Salud', null],
            ['03:00', '03:25', 'Dormir', 'Salud', null],
            ['03:30', '03:55', 'Dormir', 'Salud', null],
            ['04:00', '04:25', 'Dormir', 'Salud', null],
            ['04:30', '04:55', 'Dormir', 'Salud', null],
            ['05:00', '05:25', 'Dormir', 'Salud', null],

            // Mañana
            ['05:30', '05:55', 'Despertar', 'Salud', null],
            ['06:00', '06:25', 'Sacar mascotas', 'Familiar', null],
            ['06:30', '06:55', 'Llevar a Santiago al Colegio', 'Familiar', null],
            ['07:00', '07:25', 'Hacer desayuno', 'Hogar', null],
            ['07:30', '07:55', 'Organizar cocina', 'Hogar', null],
            ['08:00', '08:25', 'Planeación', 'Trabajo', null],
            ['08:30', '08:55', 'TE1A', 'Trabajo', null],
            ['09:00', '09:25', 'TE1B', 'Trabajo', null],
            ['09:30', '09:55', 'TE1C', 'Trabajo', null],
            ['10:00', '10:25', 'Descanso 1 - Pausa Activa', 'Salud', null],
            ['10:30', '10:55', 'TE2A', 'Trabajo', null],
            ['11:00', '11:25', 'TE2B', 'Trabajo', null],
            ['11:30', '11:55', 'TE2C', 'Trabajo', null],
            ['12:00', '12:25', 'Lectura', 'Personal', null],
            ['12:30', '12:55', 'Ejercicio', 'Personal', null],
            ['13:00', '13:25', 'Aseo personal', 'Personal', null],
            ['13:30', '13:55', 'Almuerzo', 'Hogar', null],
            ['14:00', '14:25', 'Esparcimiento', 'Personal', null],
            ['14:30', '14:55', 'TR1', 'Trabajo', null],
            ['15:00', '15:25', 'TR2', 'Trabajo', null],
            ['15:30', '15:55', 'TR3', 'Trabajo', null],
            ['16:00', '16:25', 'Descanso 2 - Pausa Activa', 'Salud', null],
            ['16:30', '16:55', 'TR4', 'Trabajo', null],
            ['17:00', '17:25', 'TR5', 'Trabajo', null],
            ['17:30', '17:55', 'TR6', 'Trabajo', null],
            ['18:00', '18:25', 'Documentación', 'Trabajo', null],
            ['18:30', '18:55', 'Cuentas', 'Personal', null],
            ['19:00', '19:25', 'Lectura', 'Personal', null],
            ['19:30', '19:55', 'Aseo casa', 'Hogar', null],
            ['20:00', '20:25', 'Hacer Comida', 'Hogar', null],
            ['20:30', '20:55', 'Familia - Comer', 'Familiar', null],
            ['21:00', '21:25', 'Familia - Comer', 'Familiar', null],
            ['21:30', '21:55', 'Aseo personal', 'Personal', null],

            // Sueño (noche)
            ['22:00', '22:25', 'Dormir', 'Salud', null],
            ['22:30', '22:55', 'Dormir', 'Salud', null],
            ['23:00', '23:25', 'Dormir', 'Salud', null],
            ['23:30', '23:55', 'Dormir', 'Salud', null],
        ]);

        // Rutina: Día Sabatino (Sat)
        $satMask = 32;
        $saturday = Routine::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'family_group_id' => null,
                'name' => 'Día Sabatino',
            ],
            [
                'description' => 'Plantilla detallada del sábado (importada de tabla).',
                'is_active' => true,
            ]
        );

        RoutineItem::query()->where('routine_id', $saturday->id)->delete();

        $this->seedItems($saturday, $satMask, [
            // Sueño (madrugada)
            ['00:00', '00:25', 'Dormir', 'Salud', null],
            ['00:30', '00:55', 'Dormir', 'Salud', null],
            ['01:00', '01:25', 'Dormir', 'Salud', null],
            ['01:30', '01:55', 'Dormir', 'Salud', null],
            ['02:00', '02:25', 'Dormir', 'Salud', null],
            ['02:30', '02:55', 'Dormir', 'Salud', null],
            ['03:00', '03:25', 'Dormir', 'Salud', null],
            ['03:30', '03:55', 'Dormir', 'Salud', null],
            ['04:00', '04:25', 'Dormir', 'Salud', null],
            ['04:30', '04:55', 'Dormir', 'Salud', null],
            ['05:00', '05:25', 'Dormir', 'Salud', null],

            // Mañana
            ['05:30', '05:55', 'Dormir', 'Salud', null],
            ['06:00', '06:25', 'Sacar mascotas', 'Familiar', null],
            ['06:30', '06:55', 'Hacer desayuno', 'Hogar', null],
            ['07:00', '07:25', 'Organizar cocina', 'Hogar', null],
            ['07:30', '07:55', 'Ejercicio', 'Personal', null],
            ['08:00', '08:25', 'Ejercicio', 'Personal', null],
            ['08:30', '08:55', 'Ejercicio', 'Personal', null],
            ['09:00', '09:25', 'Ejercicio', 'Personal', null],
            ['09:30', '09:55', 'Aseo personal', 'Personal', null],
            ['10:00', '10:25', 'Planeación emprendimiento', 'Trabajo', null],
            ['10:30', '10:55', 'Trabajo', 'Trabajo', null],
            ['11:00', '11:25', 'Trabajo', 'Trabajo', null],
            ['11:30', '11:55', 'Trabajo', 'Trabajo', null],
            ['12:00', '12:25', 'Esparcimiento', 'Personal', null],
            ['12:30', '12:55', 'Esparcimiento', 'Personal', null],
            ['13:00', '13:25', 'Almuerzo', 'Familiar', null],
            ['13:30', '13:55', 'Almuerzo', 'Familiar', null],
            ['14:00', '14:25', 'Trabajo', 'Trabajo', null],
            ['14:30', '14:55', 'Trabajo', 'Trabajo', null],
            ['15:00', '15:25', 'Trabajo', 'Trabajo', null],
            ['15:30', '15:55', 'Documentación', 'Trabajo', null],
            ['16:00', '16:25', 'Proyectos personales', 'Personal', null],
            ['16:30', '16:55', 'Proyectos personales', 'Personal', null],
            ['17:00', '17:25', 'Proyectos personales', 'Personal', null],
            ['17:30', '17:55', 'Lectura', 'Personal', null],
            ['18:00', '18:25', 'Lectura', 'Personal', null],
            ['18:30', '18:55', 'Cuentas', 'Personal', null],
            ['19:00', '19:25', 'Familia - Comer', 'Familiar', null],
            ['19:30', '19:55', 'Familia - Comer', 'Familiar', null],
            ['20:00', '20:25', 'Familia - Comer', 'Familiar', null],
            ['20:30', '20:55', 'Familia - Comer', 'Familiar', null],
            ['21:00', '21:25', 'Familia - Comer', 'Familiar', null],
            ['21:30', '21:55', 'Familia - Comer', 'Familiar', null],
            ['22:00', '22:25', 'Aseo personal', 'Personal', null],

            // Sueño (noche)
            ['22:30', '22:55', 'Dormir', 'Salud', null],
            ['23:00', '23:25', 'Dormir', 'Salud', null],
            ['23:30', '23:55', 'Dormir', 'Salud', null],
        ]);

        // No marcamos logs automáticamente en esta plantilla.

        $this->command?->info('✅ RoutinesSeeder: rutinas y bloques creados/actualizados.');
    }

    /**
     * @param array<int, array{0:string,1:string,2:string,3:string,4:string|null}> $items
     */
    private function seedItems(Routine $routine, int $weekdaysMask, array $items): void
    {
        foreach ($items as [$start, $end, $title, $group, $category]) {
            RoutineItem::query()->updateOrCreate(
                [
                    'routine_id' => $routine->id,
                    'title' => $title,
                    'start_time' => $start,
                    'end_time' => $end,
                ],
                [
                    'group' => $group,
                    'category' => $category,
                    'weekdays_mask' => $weekdaysMask,
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );
        }
    }
}
