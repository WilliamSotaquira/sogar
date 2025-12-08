<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sogar_shopping_lists', function (Blueprint $table) {
            $table->foreignId('family_group_id')
                ->nullable()
                ->after('user_id')
                ->constrained('family_groups')
                ->nullOnDelete();
            $table->index(['family_group_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('sogar_shopping_lists', function (Blueprint $table) {
            $table->dropIndex('sogar_shopping_lists_family_group_id_status_index');
            $table->dropConstrainedForeignId('family_group_id');
        });
    }
};
