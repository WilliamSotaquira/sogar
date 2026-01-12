<div>
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div class="rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 p-8 shadow-lg dark:from-amber-600 dark:to-orange-700">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold text-white/90">Productividad</p>
                    <h1 class="text-3xl font-bold">Rutinas</h1>
                    <p class="text-sm text-white/85">Agenda diaria por bloques horarios (tramos de tiempo) + checklist (hecho / saltado).</p>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-white/85">
                        <span class="hero-chip">Plantillas por día</span>
                        <span class="hero-chip">Resumen por grupo</span>
                        <span class="hero-chip">Logs por fecha</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="prevPeriod" class="inline-flex items-center justify-center rounded-lg bg-white/10 px-3 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-white/20 hover:bg-white/20" aria-label="Anterior">←</button>
                    <button type="button" wire:click="goToday" class="inline-flex items-center justify-center rounded-lg bg-white/10 px-4 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-white/20 hover:bg-white/20" aria-label="Ir a hoy">Hoy</button>
                    <button type="button" wire:click="nextPeriod" class="inline-flex items-center justify-center rounded-lg bg-white/10 px-3 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-white/20 hover:bg-white/20" aria-label="Siguiente">→</button>

                    <button type="button" wire:click="openRoutineForm" class="inline-flex items-center justify-center rounded-lg bg-white/15 px-4 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-white/20 hover:bg-white/20" aria-label="Crear nueva rutina">+ Rutina</button>
                    <button type="button" wire:click="openItemForm" class="inline-flex items-center justify-center rounded-lg bg-white/15 px-4 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-white/20 hover:bg-white/20" aria-label="Crear nuevo bloque horario">+ Bloque horario</button>
                    <button type="button" wire:click="openImportForm" class="inline-flex items-center justify-center rounded-lg bg-white/15 px-4 py-2 text-sm font-semibold text-white shadow-sm ring-1 ring-white/20 hover:bg-white/20" aria-label="Importar rutina desde tabla">Importar</button>
                </div>
            </div>

            <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-white/90">
                    <span class="text-sm">{{ $dateLabel }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs font-semibold text-white/85" for="routines_date">Fecha</label>
                    <input
                        id="routines_date"
                        type="date"
                        wire:model.live="date"
                        class="h-10 rounded-lg border border-white/20 bg-white/10 px-3 text-sm text-white shadow-sm focus:border-white/40 focus:ring-2 focus:ring-white/40"
                    />
                </div>
            </div>

            <div class="mt-4" role="tablist" aria-label="Vistas de rutinas" aria-orientation="horizontal" data-roving-tabs>
                <div class="inline-flex rounded-xl bg-white/10 p-1 ring-1 ring-white/15">
                    <button
                        type="button"
                        role="tab"
                        id="routines-tab-day"
                        wire:click="setView('day')"
                        aria-selected="{{ $view === 'day' ? 'true' : 'false' }}"
                        aria-controls="routines-panel-day"
                        tabindex="{{ $view === 'day' ? '0' : '-1' }}"
                        class="rounded-lg px-3 py-2 text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70 focus-visible:ring-offset-2 focus-visible:ring-offset-amber-600 {{ $view === 'day' ? 'bg-white text-gray-900' : 'text-white hover:bg-white/10' }}"
                        aria-label="Vista de día"
                    >Día</button>
                    <button
                        type="button"
                        role="tab"
                        id="routines-tab-timeline"
                        wire:click="setView('timeline')"
                        aria-selected="{{ $view === 'timeline' ? 'true' : 'false' }}"
                        aria-controls="routines-panel-timeline"
                        tabindex="{{ $view === 'timeline' ? '0' : '-1' }}"
                        class="rounded-lg px-3 py-2 text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70 focus-visible:ring-offset-2 focus-visible:ring-offset-amber-600 {{ $view === 'timeline' ? 'bg-white text-gray-900' : 'text-white hover:bg-white/10' }}"
                        aria-label="Vista timeline"
                    >Timeline</button>
                    <button
                        type="button"
                        role="tab"
                        id="routines-tab-week"
                        wire:click="setView('week')"
                        aria-selected="{{ $view === 'week' ? 'true' : 'false' }}"
                        aria-controls="routines-panel-week"
                        tabindex="{{ $view === 'week' ? '0' : '-1' }}"
                        class="rounded-lg px-3 py-2 text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70 focus-visible:ring-offset-2 focus-visible:ring-offset-amber-600 {{ $view === 'week' ? 'bg-white text-gray-900' : 'text-white hover:bg-white/10' }}"
                        aria-label="Vista de semana"
                    >Semana</button>
                    <button
                        type="button"
                        role="tab"
                        id="routines-tab-month"
                        wire:click="setView('month')"
                        aria-selected="{{ $view === 'month' ? 'true' : 'false' }}"
                        aria-controls="routines-panel-month"
                        tabindex="{{ $view === 'month' ? '0' : '-1' }}"
                        class="rounded-lg px-3 py-2 text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70 focus-visible:ring-offset-2 focus-visible:ring-offset-amber-600 {{ $view === 'month' ? 'bg-white text-gray-900' : 'text-white hover:bg-white/10' }}"
                        aria-label="Vista de mes"
                    >Mes</button>
                    <button
                        type="button"
                        role="tab"
                        id="routines-tab-routines"
                        wire:click="setView('routines')"
                        aria-selected="{{ $view === 'routines' ? 'true' : 'false' }}"
                        aria-controls="routines-panel-routines"
                        tabindex="{{ $view === 'routines' ? '0' : '-1' }}"
                        class="rounded-lg px-3 py-2 text-sm font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70 focus-visible:ring-offset-2 focus-visible:ring-offset-amber-600 {{ $view === 'routines' ? 'bg-white text-gray-900' : 'text-white hover:bg-white/10' }}"
                        aria-label="Vista de rutinas"
                    >Rutinas</button>
                </div>
            </div>
        </div>

        <details class="group rounded-xl border border-amber-200 bg-amber-50/70 p-4 text-sm text-gray-900 shadow-sm dark:border-amber-900/30 dark:bg-amber-900/10 dark:text-amber-50">
            <summary class="cursor-pointer select-none rounded-lg px-2 py-2 font-semibold text-gray-900 hover:bg-amber-100/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-600 focus-visible:ring-offset-2 focus-visible:ring-offset-amber-50 dark:text-amber-50 dark:hover:bg-amber-900/20 dark:focus-visible:ring-amber-400 dark:focus-visible:ring-offset-gray-950">¿Qué es una rutina y qué es un bloque?</summary>
            <div class="mt-3 grid gap-2 text-sm text-gray-900 dark:text-amber-50">
                <p><span class="font-semibold">Rutina</span>: una plantilla (Ej: “Día hábil”, “Sábado”).</p>
                <p><span class="font-semibold">Bloque horario</span>: una actividad con <span class="font-semibold">inicio/fin</span> + <span class="font-semibold">grupo</span>, que se activa para ciertos días (L, M, X…).</p>
                <p><span class="font-semibold">Checklist</span>: el estado (Hecho/Saltado) se guarda <span class="font-semibold">por fecha</span>. Semana/Mes solo resumen ese historial.</p>
            </div>
        </details>

        @if ($showRoutineForm)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900" aria-label="Formulario de rutina">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">{{ $editingRoutineId ? 'Editar' : 'Nueva' }} rutina</h2>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" id="routine_form_help">Rutina personal o compartida (según tu núcleo familiar activo).</p>
                    </div>
                    <button type="button" wire:click="closeRoutineForm" class="text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white" aria-label="Cerrar formulario de rutina">Cerrar</button>
                </div>

                <form wire:submit.prevent="saveRoutine" class="mt-4 grid gap-4 md:grid-cols-2" aria-describedby="routine_form_help">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="routine_name">Nombre <span class="text-red-600" aria-hidden="true">*</span></label>
                        <input id="routine_name" type="text" wire:model="routine_name" class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" placeholder="Ej: Día hábil" required aria-required="true" @error('routine_name') aria-invalid="true" aria-describedby="routine_name_error" @enderror />
                        @error('routine_name') <p id="routine_name_error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="routine_description">Descripción</label>
                        <textarea id="routine_description" wire:model="routine_description" rows="3" class="mt-1 block w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" placeholder="Opcional" @error('routine_description') aria-invalid="true" aria-describedby="routine_description_error" @enderror></textarea>
                        @error('routine_description') <p id="routine_description_error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="routine_is_active" type="checkbox" wire:model="routine_is_active" class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500" />
                        <label for="routine_is_active" class="text-sm text-gray-700 dark:text-gray-200">Activa</label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="routine_is_shared" type="checkbox" wire:model="routine_is_shared" class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500" />
                        <label for="routine_is_shared" class="text-sm text-gray-700 dark:text-gray-200">Compartida (núcleo activo)</label>
                    </div>

                    <div class="md:col-span-2 flex justify-end gap-2">
                        <button type="button" wire:click="closeRoutineForm" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500" wire:loading.attr="disabled" wire:target="saveRoutine" aria-label="Guardar rutina">Guardar</button>
                    </div>
                </form>
            </div>
        @endif

        @if ($showItemForm)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900" aria-label="Formulario de bloque">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">{{ $editingItemId ? 'Editar' : 'Nuevo' }} bloque horario</h2>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" id="item_form_help">Bloque de tiempo con días activos (plantilla).</p>
                    </div>
                    <button type="button" wire:click="closeItemForm" class="text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white" aria-label="Cerrar formulario de bloque">Cerrar</button>
                </div>

                <form wire:submit.prevent="saveItem" class="mt-4 grid gap-4 md:grid-cols-2" aria-describedby="item_form_help">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="item_routine_id">Rutina <span class="text-red-600" aria-hidden="true">*</span></label>
                        <select id="item_routine_id" wire:model="item_routine_id" class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" required aria-required="true" @error('item_routine_id') aria-invalid="true" aria-describedby="item_routine_id_error" @enderror>
                            <option value="">— Selecciona —</option>
                            @foreach ($routines as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}{{ $r->family_group_id ? ' (Compartida)' : '' }}</option>
                            @endforeach
                        </select>
                        @error('item_routine_id') <p id="item_routine_id_error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="item_title">Título <span class="text-red-600" aria-hidden="true">*</span></label>
                        <input id="item_title" type="text" wire:model="item_title" class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" placeholder="Ej: Desayunar" required aria-required="true" @error('item_title') aria-invalid="true" aria-describedby="item_title_error" @enderror />
                        @error('item_title') <p id="item_title_error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="item_group">Grupo <span class="text-red-600" aria-hidden="true">*</span></label>
                        <input id="item_group" type="text" wire:model="item_group" class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" placeholder="Ej: Personal" required aria-required="true" @error('item_group') aria-invalid="true" aria-describedby="item_group_error" @enderror />
                        @error('item_group') <p id="item_group_error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="item_category">Categoría</label>
                        <input id="item_category" type="text" wire:model="item_category" class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" placeholder="Ej: Salud" @error('item_category') aria-invalid="true" aria-describedby="item_category_error" @enderror />
                        @error('item_category') <p id="item_category_error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="item_start_time">Inicio <span class="text-red-600" aria-hidden="true">*</span></label>
                        <input id="item_start_time" type="time" wire:model="item_start_time" class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" required aria-required="true" @error('item_start_time') aria-invalid="true" aria-describedby="item_start_time_error" @enderror />
                        @error('item_start_time') <p id="item_start_time_error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="item_end_time">Fin <span class="text-red-600" aria-hidden="true">*</span></label>
                        <input id="item_end_time" type="time" wire:model="item_end_time" class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" required aria-required="true" @error('item_end_time') aria-invalid="true" aria-describedby="item_end_time_error" @enderror />
                        @error('item_end_time') <p id="item_end_time_error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <fieldset>
                            <legend class="block text-sm font-medium text-gray-700 dark:text-gray-300">Días <span class="text-red-600" aria-hidden="true">*</span></legend>
                            <div class="mt-2 flex flex-wrap gap-3 text-sm text-gray-700 dark:text-gray-200" role="group" aria-label="Días activos del bloque">
                            @php
                                $days = [1 => 'L', 2 => 'M', 3 => 'X', 4 => 'J', 5 => 'V', 6 => 'S', 7 => 'D'];
                            @endphp
                            @foreach ($days as $k => $label)
                                @php $id = 'weekday_' . $k; @endphp
                                <div class="inline-flex items-center gap-2">
                                    <input id="{{ $id }}" type="checkbox" value="{{ $k }}" wire:model="item_weekdays" class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500" @error('item_weekdays') aria-invalid="true" aria-describedby="item_weekdays_error" @enderror />
                                    <label for="{{ $id }}">{{ $label }}</label>
                                </div>
                            @endforeach
                            </div>
                            @error('item_weekdays') <p id="item_weekdays_error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                        </fieldset>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="item_sort_order">Orden</label>
                        <input id="item_sort_order" type="number" wire:model="item_sort_order" min="0" class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" @error('item_sort_order') aria-invalid="true" aria-describedby="item_sort_order_error" @enderror />
                        @error('item_sort_order') <p id="item_sort_order_error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="item_is_active" type="checkbox" wire:model="item_is_active" class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500" />
                        <label for="item_is_active" class="text-sm text-gray-700 dark:text-gray-200">Activo</label>
                    </div>

                    <div class="md:col-span-2 flex justify-end gap-2">
                        <button type="button" wire:click="closeItemForm" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500" wire:loading.attr="disabled" wire:target="saveItem" aria-label="Guardar bloque">Guardar</button>
                    </div>
                </form>
            </div>
        @endif

        @if ($showImportForm)
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900" aria-label="Importar rutina">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Importar desde CSV</h2>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400" id="import_help">
                            Sube un archivo CSV con columnas: Desde, Hasta, Tarea, Categoría, Estado.
                            <a href="{{ route('routines.templateTsv') }}" class="ml-1 font-semibold text-amber-700 underline decoration-amber-200 underline-offset-2 hover:text-amber-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 dark:text-amber-300 dark:hover:text-amber-200">Plantilla TSV</a>
                            <span class="text-gray-400 dark:text-gray-500">·</span>
                            <a href="{{ route('routines.templateCsv') }}" class="font-semibold text-amber-700 underline decoration-amber-200 underline-offset-2 hover:text-amber-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 dark:text-amber-300 dark:hover:text-amber-200">Plantilla CSV</a>
                        </p>
                    </div>
                    <button type="button" wire:click="closeImportForm" class="text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white" aria-label="Cerrar importación">Cerrar</button>
                </div>

                <form wire:submit.prevent="importBlocks" class="mt-4 grid gap-4" aria-describedby="import_help">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="import_file">Archivo CSV <span class="text-red-600" aria-hidden="true">*</span></label>
                        <input
                            id="import_file"
                            type="file"
                            wire:model="import_file"
                            accept=".csv,text/csv"
                            class="mt-1 block w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                            required
                            aria-required="true"
                            @error('import_file') aria-invalid="true" aria-describedby="import_file_error" @enderror
                        />
                        @error('import_file') <p id="import_file_error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Máx 5MB. Delimitador soportado: coma o punto y coma.</p>
                    </div>

                    <fieldset class="grid gap-3 md:grid-cols-2">
                        <legend class="text-sm font-semibold text-gray-900 dark:text-gray-50">Destino</legend>

                        <div class="md:col-span-2 flex flex-wrap items-center gap-4 text-sm text-gray-700 dark:text-gray-200">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" wire:model="import_mode" value="new" class="h-4 w-4 text-amber-600 focus:ring-amber-500" />
                                <span>Crear rutina nueva</span>
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" wire:model="import_mode" value="existing" class="h-4 w-4 text-amber-600 focus:ring-amber-500" />
                                <span>Añadir a rutina existente</span>
                            </label>
                            @error('import_mode') <p class="text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="import_routine_name">Nombre (si es nueva)</label>
                            <input id="import_routine_name" type="text" wire:model="import_routine_name" class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" placeholder="Ej: Día hábil" @error('import_routine_name') aria-invalid="true" aria-describedby="import_routine_name_error" @enderror />
                            @error('import_routine_name') <p id="import_routine_name_error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="import_routine_id">Rutina (si es existente)</label>
                            <select id="import_routine_id" wire:model="import_routine_id" class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" @error('import_routine_id') aria-invalid="true" aria-describedby="import_routine_id_error" @enderror>
                                <option value="">— Selecciona —</option>
                                @foreach ($routines as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                            @error('import_routine_id') <p id="import_routine_id_error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                        </div>
                    </fieldset>

                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="import_days">Días activos</label>
                            <select id="import_days" wire:model="import_days" class="mt-1 block h-12 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                <option value="weekday">Lunes a Viernes</option>
                                <option value="saturday">Sábado</option>
                                <option value="sunday">Domingo</option>
                                <option value="all">Todos los días</option>
                            </select>
                            @error('import_days') <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 self-end">
                            Tip: descarga la plantilla CSV, edítala en Excel/Sheets y luego súbela. Formato soportado: `H:MM` o `HH:MM`.
                        </div>
                    </div>

                    @if ($import_mode === 'existing')
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-800/40 dark:text-gray-200">
                            <label class="inline-flex items-start gap-2">
                                <input type="checkbox" wire:model="import_replace_existing" class="mt-1 h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500" />
                                <span>
                                    <span class="font-semibold">Reemplazar items existentes</span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">Elimina los bloques actuales de esta rutina que coinciden con los “días activos” elegidos (y su historial de logs), luego importa los nuevos.</span>
                                </span>
                            </label>
                            @error('import_replace_existing') <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-800/40 dark:text-gray-200">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <label class="inline-flex items-start gap-2">
                                <input type="checkbox" wire:model="import_apply_status" class="mt-1 h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500" />
                                <span>
                                    <span class="font-semibold">Importar “Estado” como logs</span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">Si la columna Estado dice “done/hecho” o “skipped/saltado”, se creará un log para la fecha elegida.</span>
                                </span>
                            </label>

                            <div class="sm:w-56">
                                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300" for="import_status_date">Fecha</label>
                                <input id="import_status_date" type="date" wire:model="import_status_date" class="mt-1 block h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" @if(!$import_apply_status) disabled @endif />
                                @error('import_status_date') <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        @error('import_apply_status') <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="closeImportForm" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-500" wire:loading.attr="disabled" wire:target="importBlocks" aria-label="Importar bloques">Importar</button>
                    </div>
                </form>
            </div>
        @endif

        @if (in_array($view, ['day', 'timeline'], true) && !empty($groupSummary))
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($groupSummary as $group => $s)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-50">{{ $group }}</h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $s['done_items'] }}/{{ $s['total_items'] }}</span>
                        </div>
                        <div class="mt-2">
                            <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-800">
                                @php
                                    $pct = $s['total_items'] > 0 ? (int) round(($s['done_items'] / $s['total_items']) * 100) : 0;
                                @endphp
                                <div class="h-2 rounded-full bg-amber-500" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                            {{ $s['done_minutes'] }} / {{ $s['total_minutes'] }} min · Saltados: {{ $s['skipped_items'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif

        <div id="routines-panel-day" class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900" role="tabpanel" aria-labelledby="routines-tab-day" tabindex="0" @if($view !== 'day') hidden aria-hidden="true" @else aria-hidden="false" @endif>
        @if ($view === 'day')
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Agenda del día</h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Se muestran los bloques horarios activos para el día seleccionado (según los días marcados en cada bloque).</p>
            </div>

            @if ($items->isEmpty())
                <div class="p-6 text-sm text-gray-600 dark:text-gray-300">
                    No hay bloques horarios para este día. Crea una rutina y añade bloques marcando el día correspondiente.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <caption class="sr-only">Listado de agenda del día con hora, actividad, grupo, rutina y estado.</caption>
                        <thead class="bg-gray-50 dark:bg-gray-800/40">
                            <tr>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Hora</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Actividad</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Grupo</th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Rutina</th>
                                <th scope="col" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($items as $item)
                                @php
                                    $log = $item->logs->first();
                                    $status = $log?->status;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40" wire:key="routine-item-{{ $item->id }}">
                                    <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-200">
                                        <span class="font-semibold">{{ \Illuminate\Support\Str::of((string) $item->start_time)->substr(0,5) }}</span>
                                        <span class="text-gray-400">→</span>
                                        <span class="font-semibold">{{ \Illuminate\Support\Str::of((string) $item->end_time)->substr(0,5) }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-50">{{ $item->title }}</div>
                                        <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            @if ($item->category)
                                                {{ $item->category }} ·
                                            @endif
                                            {{ $item->duration_minutes ?? 0 }} min
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $item->group }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $item->routine?->name }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <button type="button" wire:click="toggleDone({{ $item->id }})" aria-pressed="{{ $status === 'done' ? 'true' : 'false' }}" aria-label="Marcar como hecho: {{ $item->title }}" class="rounded-lg px-3 py-2 text-xs font-semibold ring-1 {{ $status === 'done' ? 'bg-emerald-600 text-white ring-emerald-600' : 'bg-white text-emerald-700 ring-emerald-200 hover:bg-emerald-50 dark:bg-gray-900 dark:text-emerald-300 dark:ring-emerald-900/40 dark:hover:bg-emerald-900/10' }}">
                                                Hecho
                                            </button>
                                            <button type="button" wire:click="markSkipped({{ $item->id }})" aria-pressed="{{ $status === 'skipped' ? 'true' : 'false' }}" aria-label="Marcar como saltado: {{ $item->title }}" class="rounded-lg px-3 py-2 text-xs font-semibold ring-1 {{ $status === 'skipped' ? 'bg-gray-700 text-white ring-gray-700' : 'bg-white text-gray-700 ring-gray-200 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-800' }}">
                                                Saltar
                                            </button>
                                            @if ($status)
                                                <button type="button" wire:click="clearStatus({{ $item->id }})" aria-label="Quitar estado: {{ $item->title }}" class="rounded-lg px-3 py-2 text-xs font-semibold bg-white text-rose-700 ring-1 ring-rose-200 hover:bg-rose-50 dark:bg-gray-900 dark:text-rose-300 dark:ring-rose-900/40 dark:hover:bg-rose-900/10">
                                                    Deshacer
                                                </button>
                                            @endif

                                            <button type="button" wire:click="openItemForm({{ $item->id }})" aria-label="Editar bloque horario: {{ $item->title }}" class="rounded-lg px-3 py-2 text-xs font-semibold bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-800">
                                                Editar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
        </div>

        <div id="routines-panel-timeline" class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900" role="tabpanel" aria-labelledby="routines-tab-timeline" tabindex="0" @if($view !== 'timeline') hidden aria-hidden="true" @else aria-hidden="false" @endif>
        @if ($view === 'timeline')
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Timeline</h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Vista del día en formato cronológico (mejor para móvil).</p>
                </div>

                @if ($items->isEmpty())
                    <div class="p-6 text-sm text-gray-600 dark:text-gray-300">
                        No hay bloques para este día. Crea una rutina y añade bloques con el día marcado.
                    </div>
                @else
                    <ol class="divide-y divide-gray-100 dark:divide-gray-800" aria-label="Bloques del día en timeline">
                        @foreach ($items as $item)
                            @php
                                $log = $item->logs->first();
                                $status = $log?->status;
                            @endphp
                            <li class="p-5" wire:key="timeline-item-{{ $item->id }}">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="flex items-start gap-4">
                                        <div class="w-20 shrink-0">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-50">{{ \Illuminate\Support\Str::of((string) $item->start_time)->substr(0,5) }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ \Illuminate\Support\Str::of((string) $item->end_time)->substr(0,5) }}</div>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-50">{{ $item->title }}</h3>
                                                @if ($status === 'done')
                                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200">Hecho</span>
                                                @elseif ($status === 'skipped')
                                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-200">Saltado</span>
                                                @endif
                                            </div>
                                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $item->group }}
                                                @if ($item->category)
                                                    · {{ $item->category }}
                                                @endif
                                                · {{ $item->duration_minutes ?? 0 }} min
                                                @if ($item->routine)
                                                    · {{ $item->routine->name }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <div class="inline-flex items-center gap-2">
                                        <button type="button" wire:click="toggleDone({{ $item->id }})" aria-pressed="{{ $status === 'done' ? 'true' : 'false' }}" aria-label="Marcar como hecho: {{ $item->title }}" class="rounded-lg px-3 py-2 text-xs font-semibold ring-1 {{ $status === 'done' ? 'bg-emerald-600 text-white ring-emerald-600' : 'bg-white text-emerald-700 ring-emerald-200 hover:bg-emerald-50 dark:bg-gray-900 dark:text-emerald-300 dark:ring-emerald-900/40 dark:hover:bg-emerald-900/10' }}">Hecho</button>
                                        <button type="button" wire:click="markSkipped({{ $item->id }})" aria-pressed="{{ $status === 'skipped' ? 'true' : 'false' }}" aria-label="Marcar como saltado: {{ $item->title }}" class="rounded-lg px-3 py-2 text-xs font-semibold ring-1 {{ $status === 'skipped' ? 'bg-gray-700 text-white ring-gray-700' : 'bg-white text-gray-700 ring-gray-200 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-800' }}">Saltar</button>
                                        @if ($status)
                                            <button type="button" wire:click="clearStatus({{ $item->id }})" aria-label="Quitar estado: {{ $item->title }}" class="rounded-lg px-3 py-2 text-xs font-semibold bg-white text-rose-700 ring-1 ring-rose-200 hover:bg-rose-50 dark:bg-gray-900 dark:text-rose-300 dark:ring-rose-900/40 dark:hover:bg-rose-900/10">Deshacer</button>
                                        @endif
                                        <button type="button" wire:click="openItemForm({{ $item->id }})" aria-label="Editar bloque horario: {{ $item->title }}" class="rounded-lg px-3 py-2 text-xs font-semibold bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-800">Editar</button>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
        @endif
        </div>

        <div id="routines-panel-week" class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900" role="tabpanel" aria-labelledby="routines-tab-week" tabindex="0" @if($view !== 'week') hidden aria-hidden="true" @else aria-hidden="false" @endif>
        @if ($view === 'week')
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Semana</h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Checklist y resumen por día (usa Hecho/Saltar por bloque).</p>
                </div>

                @if (empty($weekDays))
                    <div class="p-6 text-sm text-gray-600 dark:text-gray-300">
                        No hay rutinas visibles para mostrar en la semana.
                    </div>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($weekDays as $day)
                            @php
                                $dayKey = $day->toDateString();
                                $dayItems = $weekItems[$dayKey] ?? collect();
                                $s = $weekDaySummary[$dayKey] ?? null;
                            @endphp

                            <section class="p-5" aria-label="{{ $day->translatedFormat('l j M') }}">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-50">{{ $day->translatedFormat('l j M') }}</h3>
                                        @if ($s)
                                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $s['done_items'] }}/{{ $s['total_items'] }} · {{ $s['done_minutes'] }}/{{ $s['total_minutes'] }} min · Saltados: {{ $s['skipped_items'] }}
                                            </p>
                                        @endif
                                    </div>
                                    <button type="button" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700" wire:click="openDay('{{ $dayKey }}')" aria-label="Abrir este día">Ver día</button>
                                </div>

                                @if ($dayItems->isEmpty())
                                    <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">Sin bloques activos.</p>
                                @else
                                    <div class="mt-3 overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                            <caption class="sr-only">Bloques del día {{ $day->translatedFormat('l j M') }}</caption>
                                            <thead class="bg-gray-50 dark:bg-gray-800/40">
                                                <tr>
                                                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Hora</th>
                                                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Bloque</th>
                                                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Grupo</th>
                                                    <th scope="col" class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Rutina</th>
                                                    <th scope="col" class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                                @foreach ($dayItems as $item)
                                                    @php
                                                        $status = $weekItemStatus[$dayKey][$item->id] ?? null;
                                                    @endphp
                                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40" wire:key="week-item-{{ $dayKey }}-{{ $item->id }}">
                                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">
                                                            <span class="font-semibold">{{ \Illuminate\Support\Str::of((string) $item->start_time)->substr(0,5) }}</span>
                                                            <span class="text-gray-400">→</span>
                                                            <span class="font-semibold">{{ \Illuminate\Support\Str::of((string) $item->end_time)->substr(0,5) }}</span>
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-50">{{ $item->title }}</div>
                                                            <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                                @if ($item->category)
                                                                    {{ $item->category }} ·
                                                                @endif
                                                                {{ $item->duration_minutes ?? 0 }} min
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $item->group }}</td>
                                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $item->routine?->name }}</td>
                                                        <td class="px-4 py-3 text-right">
                                                            <div class="inline-flex items-center gap-2">
                                                                <button type="button" wire:click="toggleDone({{ $item->id }}, '{{ $dayKey }}')" aria-pressed="{{ $status === 'done' ? 'true' : 'false' }}" aria-label="Marcar como hecho: {{ $item->title }}" class="rounded-lg px-3 py-2 text-xs font-semibold ring-1 {{ $status === 'done' ? 'bg-emerald-600 text-white ring-emerald-600' : 'bg-white text-emerald-700 ring-emerald-200 hover:bg-emerald-50 dark:bg-gray-900 dark:text-emerald-300 dark:ring-emerald-900/40 dark:hover:bg-emerald-900/10' }}">Hecho</button>
                                                                <button type="button" wire:click="markSkipped({{ $item->id }}, '{{ $dayKey }}')" aria-pressed="{{ $status === 'skipped' ? 'true' : 'false' }}" aria-label="Marcar como saltado: {{ $item->title }}" class="rounded-lg px-3 py-2 text-xs font-semibold ring-1 {{ $status === 'skipped' ? 'bg-gray-700 text-white ring-gray-700' : 'bg-white text-gray-700 ring-gray-200 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-800' }}">Saltar</button>
                                                                @if ($status)
                                                                    <button type="button" wire:click="clearStatus({{ $item->id }}, '{{ $dayKey }}')" aria-label="Quitar estado: {{ $item->title }}" class="rounded-lg px-3 py-2 text-xs font-semibold bg-white text-rose-700 ring-1 ring-rose-200 hover:bg-rose-50 dark:bg-gray-900 dark:text-rose-300 dark:ring-rose-900/40 dark:hover:bg-rose-900/10">Deshacer</button>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </section>
                        @endforeach
                    </div>
                @endif
        @endif
        </div>

        <div id="routines-panel-month" class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900" role="tabpanel" aria-labelledby="routines-tab-month" tabindex="0" @if($view !== 'month') hidden aria-hidden="true" @else aria-hidden="false" @endif>
        @if ($view === 'month')
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-50">Mes</h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Resumen diario (click en un día para abrir la agenda).</p>
                </div>

                @php
                    $dayLabels = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
                @endphp

                @if (empty($monthWeeks))
                    <div class="p-6 text-sm text-gray-600 dark:text-gray-300">No hay rutinas visibles para mostrar en el mes.</div>
                @else
                    <div class="p-5">
                        <div class="grid grid-cols-7 gap-2 text-xs font-semibold text-gray-500 dark:text-gray-300" aria-hidden="true">
                            @foreach ($dayLabels as $dl)
                                <div class="px-2 py-1">{{ $dl }}</div>
                            @endforeach
                        </div>

                        <div class="mt-2 grid grid-cols-7 gap-2" role="grid" aria-label="Calendario mensual">
                            @foreach ($monthWeeks as $week)
                                @foreach ($week as $day)
                                    @php
                                        $dayKey = $day->toDateString();
                                        $s = $monthDaySummary[$dayKey] ?? ['total_items' => 0, 'done_items' => 0, 'skipped_items' => 0, 'total_minutes' => 0, 'done_minutes' => 0, 'state' => 'empty'];
                                        $inMonth = $day->month === (int) $currentMonth;
                                        $isSelected = $dayKey === (string) $currentDate;
                                        $pct = $s['total_items'] > 0 ? (int) round(($s['done_items'] / $s['total_items']) * 100) : 0;

                                        $stateDot = match ($s['state'] ?? 'empty') {
                                            'all_done' => 'bg-emerald-500',
                                            'partial' => 'bg-amber-500',
                                            'skipped_only' => 'bg-gray-500',
                                            'pending' => 'bg-slate-400',
                                            default => 'bg-transparent',
                                        };
                                    @endphp

                                    <button
                                        type="button"
                                        role="gridcell"
                                        wire:click="openDay('{{ $dayKey }}')"
                                        class="group rounded-xl border px-2 py-2 text-left shadow-sm transition focus:outline-none focus:ring-2 focus:ring-amber-500/70 dark:focus:ring-amber-400/60
                                            {{ $inMonth ? 'border-gray-200 bg-white hover:bg-amber-50/40 dark:border-gray-800 dark:bg-gray-900 dark:hover:bg-gray-800/40' : 'border-gray-100 bg-gray-50 text-gray-400 dark:border-gray-900 dark:bg-gray-900/40 dark:text-gray-500' }}
                                            {{ $isSelected ? 'ring-2 ring-amber-500/70 dark:ring-amber-400/60' : '' }}"
                                        aria-label="{{ $day->translatedFormat('l j F Y') }}. Hechos {{ $s['done_items'] }} de {{ $s['total_items'] }}"
                                    >
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-semibold {{ $inMonth ? 'text-gray-900 dark:text-gray-50' : 'text-gray-400 dark:text-gray-500' }}">{{ $day->day }}</span>
                                            @if ($s['total_items'] > 0)
                                                <span class="inline-flex items-center gap-2">
                                                    <span class="h-2 w-2 rounded-full {{ $stateDot }}" aria-hidden="true"></span>
                                                    <span class="text-[11px] font-semibold {{ $inMonth ? 'text-gray-600 dark:text-gray-300' : 'text-gray-400 dark:text-gray-500' }}">{{ $s['done_items'] }}/{{ $s['total_items'] }}</span>
                                                </span>
                                            @endif
                                        </div>

                                        @if ($s['total_items'] > 0)
                                            <div class="mt-2 h-2 w-full rounded-full bg-gray-200 dark:bg-gray-800" aria-hidden="true">
                                                <div class="h-2 rounded-full bg-amber-500" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <div class="mt-1 text-[11px] {{ $inMonth ? 'text-gray-500 dark:text-gray-400' : 'text-gray-400 dark:text-gray-500' }}">
                                                {{ $s['done_minutes'] }}/{{ $s['total_minutes'] }} min
                                                @if ($s['skipped_items'] > 0)
                                                    · Salt: {{ $s['skipped_items'] }}
                                                @endif
                                            </div>
                                        @else
                                            <div class="mt-2 text-[11px] {{ $inMonth ? 'text-gray-500 dark:text-gray-400' : 'text-gray-400 dark:text-gray-500' }}">Sin bloques</div>
                                        @endif
                                    </button>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                @endif
        @endif
        </div>

        <div id="routines-panel-routines" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900" role="tabpanel" aria-labelledby="routines-tab-routines" tabindex="0" @if($view !== 'routines') hidden aria-hidden="true" @else aria-hidden="false" @endif>
        @if ($view === 'routines')
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-50">Rutinas disponibles</h2>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Tip: crea 2 rutinas típicas (día hábil / sábado) y activa los días desde los bloques.</p>

            @if ($routines->isEmpty())
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">Aún no tienes rutinas. Crea una con “+ Rutina”.</p>
            @else
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach ($routines as $r)
                        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900" wire:key="routine-card-{{ $r->id }}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-50">{{ $r->name }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $r->family_group_id ? 'Compartida' : 'Personal' }} · {{ $r->is_active ? 'Activa' : 'Inactiva' }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('routines.exportTsv', ['routine' => $r->id]) }}" class="rounded-lg px-3 py-2 text-xs font-semibold bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-800" aria-label="Exportar rutina TSV: {{ $r->name }}">TSV</a>
                                    <a href="{{ route('routines.exportCsv', ['routine' => $r->id]) }}" class="rounded-lg px-3 py-2 text-xs font-semibold bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-800" aria-label="Exportar rutina CSV: {{ $r->name }}">CSV</a>
                                    <button type="button" wire:click="openRoutineForm({{ $r->id }})" aria-label="Editar rutina: {{ $r->name }}" class="rounded-lg px-3 py-2 text-xs font-semibold bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-800">Editar</button>
                                </div>
                            </div>
                            @if ($r->description)
                                <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">{{ $r->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
        </div>
    </div>
</div>
