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

        DB::transaction(function () use ($rows, $request, $userId, $get, $parseFloat, $parseDate, &$imported, &$skipped) {
            foreach ($rows as $row) {
                $barcode = trim((string) ($get($row, 'product_barcode') ?? ''));
                $name = trim((string) ($get($row, 'product_name') ?? ''));

                if ($barcode === '' && $name === '') {
                    $skipped++;
                    continue;
                }

                $product = null;
                if ($barcode !== '') {
                    $product = FoodProduct::where('user_id', $userId)->where('barcode', $barcode)->first();
                }
                if (!$product && $name !== '') {
                    $product = FoodProduct::where('user_id', $userId)->where('name', $name)->first();
                }

                if (!$product) {
                    $skipped++;
                    continue;
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

        return redirect()->route('food.inventory.index')->with('status', "Inventario importado: {$imported} ítem(s). Omitidos: {$skipped}.");
    }
}
