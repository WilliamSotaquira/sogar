<?php

namespace App\Http\Controllers\Food;

use App\Http\Controllers\Controller;
use App\Models\FoodLocation;
use App\Models\FoodProduct;
use App\Models\FoodStockBatch;
use App\Models\FoodType;
use App\Models\ShoppingListItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $locationId = $request->input('location_id');
        $typeId = $request->input('type_id');

        $batches = FoodStockBatch::with(['product.type', 'location'])
            ->where('user_id', $userId)
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->when($typeId, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('type_id', $typeId)))
            ->orderBy('expires_on')
            ->get();

        $products = FoodProduct::where('user_id', $userId)->orderBy('name')->get();
        $locations = FoodLocation::where('user_id', $userId)->orderBy('sort_order')->get();
        $types = FoodType::where('user_id', $userId)->orderBy('sort_order')->get();

        $activeLocation = $locations->firstWhere('id', (int) $locationId);
        $activeType = $types->firstWhere('id', (int) $typeId);

        $history = collect(session('inventory_filters_history', []));
        if ($activeLocation || $activeType) {
            $entry = [
                'location_id' => $activeLocation?->id,
                'type_id' => $activeType?->id,
                'label' => trim(($activeLocation?->name ?? 'Todas las ubicaciones') . ($activeType ? ' · ' . $activeType->name : '')),
            ];

            $history = $history->reject(fn ($item) => $item['location_id'] === $entry['location_id'] && $item['type_id'] === $entry['type_id'])
                ->prepend($entry)
                ->take(5);

            session(['inventory_filters_history' => $history->values()->all()]);
        }

        $user = $request->user();
        $familyGroupIds = method_exists($user, 'familyGroupIds') ? $user->familyGroupIds() : [];

        $pendingInventoryPool = ShoppingListItem::with(['list', 'product.defaultLocation', 'location'])
            ->where('is_checked', true)
            ->whereHas('list', function ($query) use ($user, $familyGroupIds) {
                $query->where(function ($inner) use ($user, $familyGroupIds) {
                    $inner->where('user_id', $user->id);

                    if (!empty($familyGroupIds)) {
                        $inner->orWhereIn('family_group_id', $familyGroupIds);
                    }
                });
            })
            ->latest('checked_at')
            ->take(30)
            ->get()
            ->filter(fn ($item) => empty(data_get($item->metadata, 'inventory_batch_id')))
            ->values();

        $pendingListFilterId = (int) $request->input('pending_list_id');

        $pendingInventoryItems = $pendingInventoryPool;
        if ($pendingListFilterId) {
            $pendingInventoryItems = $pendingInventoryPool
                ->where('shopping_list_id', $pendingListFilterId)
                ->values();
        }

        $pendingInventoryFilterOptions = $pendingInventoryPool
            ->pluck('list')
            ->filter()
            ->unique('id')
            ->values();

        return view('food.inventory.index', [
            'batches' => $batches,
            'products' => $products,
            'locations' => $locations,
            'types' => $types,
            'activeLocation' => $activeLocation,
            'activeType' => $activeType,
            'filterHistory' => $history,
            'pendingInventoryItems' => $pendingInventoryItems,
            'pendingInventoryCount' => $pendingInventoryPool->count(),
            'pendingInventoryFilterOptions' => $pendingInventoryFilterOptions,
            'activePendingListId' => $pendingListFilterId,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $userId = $request->user()->id;
        $locationId = $request->input('location_id');
        $typeId = $request->input('type_id');

        $batches = FoodStockBatch::with(['product.type', 'product.defaultLocation', 'location'])
            ->where('user_id', $userId)
            ->when($locationId, fn ($q) => $q->where('location_id', $locationId))
            ->when($typeId, fn ($q) => $q->whereHas('product', fn ($p) => $p->where('type_id', $typeId)))
            ->orderBy('expires_on')
            ->get();

        $fileName = 'inventory-export-' . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($batches) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 para Excel
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'producto',
                'codigo',
                'ubicacion',
                'cantidad',
                'unidad',
                'caduca',
                'ingreso',
                'estado',
            ], ';');

            foreach ($batches as $batch) {
                $locationName = $batch->location?->name
                    ?? $batch->product?->defaultLocation?->name
                    ?? '';

                $barcode = $this->formatSpreadsheetCode($batch->product?->barcode);

                fputcsv($out, [
                    $batch->product?->name ?? 'Producto eliminado',
                    $barcode,
                    $locationName,
                    (string) $batch->qty_remaining_base,
                    $batch->unit_base ?? '',
                    $batch->expires_on ? $batch->expires_on->toDateString() : '',
                    $batch->entered_on ? $batch->entered_on->toDateString() : '',
                    $batch->status ?? '',
                ], ';');
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function templateCsv(Request $request)
    {
        $fileName = 'inventory-template.csv';

        $callback = function () {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 para Excel
            fwrite($out, "\xEF\xBB\xBF");

            // Formato mínimo
            fputcsv($out, ['producto', 'cantidad', 'ubicacion', 'codigo', 'caduca'], ';');
            fputcsv($out, ['Arroz', '2', '', '', ''], ';');

            fclose($out);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:4096', 'mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel,application/octet-stream'],
        ]);

        return $this->importFromCsv($request);
    }

    private function importFromCsv(Request $request): RedirectResponse
    {
        $userId = $request->user()->id;
        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');

        if (!$handle) {
            return back()->withErrors(['file' => 'No se pudo leer el archivo.']);
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return back()->withErrors(['file' => 'El archivo está vacío.']);
        }

        $delimiters = [
            ';' => substr_count($firstLine, ';'),
            ',' => substr_count($firstLine, ','),
            "\t" => substr_count($firstLine, "\t"),
        ];
        arsort($delimiters);
        $delimiter = array_key_first($delimiters) ?: ';';

        rewind($handle);
        $firstRow = fgetcsv($handle, 0, $delimiter);
        if (!$firstRow || count($firstRow) < 1) {
            fclose($handle);
            return back()->withErrors(['file' => 'CSV inválido: no se pudo leer la cabecera.']);
        }

        $headers = array_map(fn($h) => trim((string) $h), $firstRow);

        $normalizeHeader = function (string $header): string {
            $header = trim(mb_strtolower($header));
            $header = str_replace([' ', '-', '.', ':'], '_', $header);
            $header = preg_replace('/_+/', '_', $header);
            $header = trim($header, '_');
            $header = strtr($header, [
                'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
                'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
                'ñ' => 'n',
            ]);
            return $header;
        };

        $normalized = [];
        foreach ($headers as $idx => $header) {
            $normalized[$normalizeHeader($header)] = $idx;
        }

        $findIndex = function (array $aliases) use ($normalized, $normalizeHeader): ?int {
            foreach ($aliases as $alias) {
                $key = $normalizeHeader((string) $alias);
                if (array_key_exists($key, $normalized)) {
                    return $normalized[$key];
                }
            }
            return null;
        };

        $col = [
            'product_name' => $findIndex(['producto', 'product', 'item_name', 'name']),
            'product_barcode' => $findIndex(['codigo', 'codigo_barras', 'barcode', 'ean', 'upc', 'product_barcode']),
            'qty' => $findIndex(['cantidad', 'qty', 'qty_base']),
            'location_name' => $findIndex(['ubicacion', 'location', 'location_name']),
            'expires_on' => $findIndex(['caduca', 'vence', 'expires_on', 'expiry_date']),
        ];

        if ($col['product_name'] === null && $col['product_barcode'] === null) {
            fclose($handle);
            return back()->withErrors(['file' => 'CSV mínimo inválido: agrega la columna "producto" o "codigo".']);
        }

        $get = function(array $row, string $key) use ($col) {
            $i = $col[$key] ?? null;
            if ($i === null) return null;
            return array_key_exists($i, $row) ? $row[$i] : null;
        };

        $parseFloat = function($value): float {
            $v = trim((string) $value);
            if ($v === '') return 0.0;
            $v = str_replace(['.', ','], ['.', '.'], $v);
            return (float) $v;
        };

        $parseDate = function ($value): ?string {
            $v = trim((string) $value);
            if ($v === '') return null;
            $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y'];
            foreach ($formats as $fmt) {
                try {
                    $dt = \Carbon\Carbon::createFromFormat($fmt, $v);
                    return $dt->toDateString();
                } catch (\Throwable $e) {
                    // try next
                }
            }
            try {
                return \Carbon\Carbon::parse($v)->toDateString();
            } catch (\Throwable $e) {
                return null;
            }
        };

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($row) === 1 && trim((string) $row[0]) === '') continue;
            $rows[] = $row;
        }
        fclose($handle);

        if (empty($rows)) {
            return back()->withErrors(['file' => 'El CSV no tiene filas para importar.']);
        }

        $imported = 0;
        $skipped = 0;
        $skippedEmpty = 0;
        $skippedNotFound = 0;
        $skippedDuplicateName = 0;
        $skippedSamples = [];

        DB::transaction(function () use ($rows, $request, $userId, $get, $parseFloat, $parseDate, &$imported, &$skipped, &$skippedEmpty, &$skippedNotFound, &$skippedDuplicateName, &$skippedSamples) {
            foreach ($rows as $row) {
                $barcode = $this->normalizeSpreadsheetCode($get($row, 'product_barcode') ?? '');
                $name = trim((string) ($get($row, 'product_name') ?? ''));

                if ($barcode === '' && $name === '') {
                    $skipped++;
                    $skippedEmpty++;
                    continue;
                }

                // 1) Buscar por código (si existe)
                $product = null;
                if ($barcode !== '') {
                    $product = FoodProduct::where('user_id', $userId)->where('barcode', $barcode)->first();
                }

                // 2) Si no se encontró por código, intentar por nombre (case-insensitive)
                if (!$product && $name !== '') {
                    $byName = FoodProduct::where('user_id', $userId)
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                        ->get();

                    if ($byName->count() > 1) {
                        // Única restricción: nombre duplicado (ambiguo). Pide corregir o usar código.
                        $skipped++;
                        $skippedDuplicateName++;
                        if (count($skippedSamples) < 5) {
                            $skippedSamples[] = $name;
                        }
                        continue;
                    }

                    $product = $byName->first();

                    // Si encontramos por nombre y trae un barcode nuevo, intentar asignarlo si está libre.
                    if ($product && $barcode !== '' && empty($product->barcode)) {
                        $barcodeInUse = FoodProduct::where('user_id', $userId)->where('barcode', $barcode)->exists();
                        if (!$barcodeInUse) {
                            $product->barcode = $barcode;
                            $product->save();
                        }
                    }
                }

                // 3) Si no existe, crear producto automáticamente (sin restricción, salvo nombre duplicado).
                if (!$product) {
                    $createName = $name !== '' ? $name : ('Producto ' . $barcode);

                    // Si el nombre ya existe en más de un producto, evitar crear por ambigüedad.
                    if ($createName !== '') {
                        $nameCount = FoodProduct::where('user_id', $userId)
                            ->whereRaw('LOWER(name) = ?', [mb_strtolower($createName)])
                            ->count();
                        if ($nameCount > 0) {
                            // Ya existe al menos uno: usar el existente si es único.
                            $existing = FoodProduct::where('user_id', $userId)
                                ->whereRaw('LOWER(name) = ?', [mb_strtolower($createName)])
                                ->get();
                            if ($existing->count() === 1) {
                                $product = $existing->first();
                            } else {
                                $skipped++;
                                $skippedDuplicateName++;
                                if (count($skippedSamples) < 5) {
                                    $skippedSamples[] = $createName;
                                }
                                continue;
                            }
                        }
                    }

                    if (!$product) {
                        // Respetar unicidad de barcode si viene.
                        $createBarcode = $barcode !== '' ? $barcode : null;
                        if ($createBarcode !== null) {
                            $barcodeInUse = FoodProduct::where('user_id', $userId)->where('barcode', $createBarcode)->exists();
                            if ($barcodeInUse) {
                                $createBarcode = null;
                            }
                        }

                        $product = FoodProduct::create([
                            'user_id' => $userId,
                            'name' => $createName !== '' ? $createName : 'Producto sin nombre',
                            'barcode' => $createBarcode,
                            'unit_base' => 'unit',
                            'unit_size' => 1,
                            'is_active' => true,
                        ]);
                    }
                }

                $qty = $parseFloat($get($row, 'qty') ?? 1);
                if ($qty <= 0) $qty = 1;

                $locationName = trim((string) ($get($row, 'location_name') ?? ''));
                $locationId = null;
                if ($locationName !== '') {
                    $locationId = FoodLocation::where('user_id', $userId)
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($locationName)])
                        ->value('id');
                }
                if (!$locationId) {
                    $locationId = $product->default_location_id;
                }

                $expiresOn = $parseDate($get($row, 'expires_on'));

                FoodStockBatch::create([
                    'user_id' => $userId,
                    'product_id' => $product->id,
                    'location_id' => $locationId,
                    'qty_base' => $qty,
                    'qty_remaining_base' => $qty,
                    'unit_base' => $product->unit_base ?? 'unit',
                    'entered_on' => now()->toDateString(),
                    'expires_on' => $expiresOn,
                    'status' => 'ok',
                ]);

                $imported++;
            }
        });

        if ($imported === 0) {
            return back()->withErrors(['file' => 'No se importó ningún ítem (verifica nombres o códigos).']);
        }

        $details = [];
        if ($skippedEmpty > 0) {
            $details[] = "sin producto/código: {$skippedEmpty}";
        }
        if ($skippedDuplicateName > 0) {
            $details[] = "nombre duplicado: {$skippedDuplicateName}";
        }
        if ($skippedNotFound > 0) {
            $details[] = "producto no encontrado: {$skippedNotFound}";
        }
        $detailText = empty($details) ? '' : ' (' . implode(', ', $details) . ')';
        $sampleText = empty($skippedSamples) ? '' : ' Ej: ' . implode(', ', array_slice($skippedSamples, 0, 3)) . '.';

        return redirect()->route('food.inventory.index')->with('status', "Inventario importado: {$imported} ítem(s). Omitidos: {$skipped}{$detailText}.{$sampleText}");
    }

    private function formatSpreadsheetCode(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        // Evita notación científica en Excel/Sheets para códigos largos (EAN/UPC)
        if (preg_match('/^\d{10,}$/', $value)) {
            return '="' . $value . '"';
        }

        return $value;
    }

    private function normalizeSpreadsheetCode(mixed $value): string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return '';
        }

        // Limpiar separadores comunes
        $v = preg_replace('/[\s\-]+/', '', $v);

        // Sheets/Excel pueden exportar texto forzado como: ="7702..."
        if (preg_match('/^\s*=\s*"(.*)"\s*$/', $v, $m)) {
            $v = $m[1];
        }

        // También puede venir con apóstrofe de "texto": '7702...
        if (str_starts_with($v, "'")) {
            $v = substr($v, 1);
        }

        $v = trim($v);

        // Notación científica (p.ej. 7,702E+12)
        if (preg_match('/^[0-9]+([\.,][0-9]+)?[eE][\+\-]?[0-9]+$/', $v)) {
            $expanded = $this->expandScientificNotation($v);
            $expanded = str_replace(['.', '+', '-'], '', $expanded);
            $v = $expanded;
        }

        return $v;
    }

    private function expandScientificNotation(string $value): string
    {
        $value = trim($value);
        $value = str_replace(',', '.', $value);

        if (!preg_match('/^([\+\-]?)(\d+)(?:\.(\d+))?[eE]([\+\-]?\d+)$/', $value, $m)) {
            return $value;
        }

        $sign = $m[1] ?? '';
        $int = $m[2] ?? '0';
        $frac = $m[3] ?? '';
        $exp = (int) ($m[4] ?? 0);

        $digits = $int . $frac;
        $decPlaces = strlen($frac);
        $shift = $exp - $decPlaces;

        if ($shift >= 0) {
            $plain = $digits . str_repeat('0', $shift);
        } else {
            $pos = strlen($digits) + $shift;
            if ($pos <= 0) {
                $plain = '0.' . str_repeat('0', -$pos) . $digits;
            } else {
                $plain = substr($digits, 0, $pos) . '.' . substr($digits, $pos);
            }
        }

        return $sign === '-' ? '-' . $plain : $plain;
    }
}
