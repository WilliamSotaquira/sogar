<?php

namespace App\Http\Controllers\Routines;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoutineTemplateController extends Controller
{
    public function tsv(Request $request)
    {
        $fileName = 'routines-template.tsv';

        $callback = function () {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM para que Excel/Sheets detecten correctamente.
            fwrite($out, "\xEF\xBB\xBF");

            $delimiter = "\t";

            // Columnas esperadas por el importador (se puede pegar directo desde Excel/Sheets).
            fputcsv($out, ['Desde', 'Hasta', 'Tarea', 'Categoría', 'Estado'], $delimiter);

            // Ejemplos mínimos
            fputcsv($out, ['05:30', '05:55', 'Despertar', 'Salud', ''], $delimiter);
            fputcsv($out, ['06:00', '06:25', 'Meditación', 'Personal', 'done'], $delimiter);

            fclose($out);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/tab-separated-values; charset=UTF-8',
        ]);
    }

    public function csv(Request $request)
    {
        $fileName = 'routines-template.csv';

        $callback = function () {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM para que Excel/Sheets detecten correctamente.
            fwrite($out, "\xEF\xBB\xBF");

            // En muchas configuraciones regionales (ES/LatAm) Excel usa ';' como separador.
            $delimiter = ';';

            fputcsv($out, ['Desde', 'Hasta', 'Tarea', 'Categoría', 'Estado'], $delimiter);
            fputcsv($out, ['05:30', '05:55', 'Despertar', 'Salud', ''], $delimiter);
            fputcsv($out, ['06:00', '06:25', 'Meditación', 'Personal', 'done'], $delimiter);

            fclose($out);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
