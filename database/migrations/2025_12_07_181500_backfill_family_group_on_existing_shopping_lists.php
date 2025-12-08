<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('sogar_shopping_lists')
            ->whereNull('family_group_id')
            ->orderBy('id')
            ->chunkById(200, function ($lists) {
                foreach ($lists as $list) {
                    $familyId = DB::table('users')
                        ->where('id', $list->user_id)
                        ->value('active_family_group_id');

                    if ($familyId) {
                        DB::table('sogar_shopping_lists')
                            ->where('id', $list->id)
                            ->update(['family_group_id' => $familyId]);
                    }
                }
            });
    }

    public function down(): void
    {
        // No revertimos datos para evitar perder asociaciones manuales.
    }
};
