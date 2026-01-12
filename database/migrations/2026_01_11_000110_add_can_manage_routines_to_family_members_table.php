<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            $table->boolean('can_manage_routines')->default(false)->after('can_manage_habits');
            $table->index(['family_group_id', 'can_manage_routines']);
        });
    }

    public function down(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            $table->dropIndex('family_members_family_group_id_can_manage_routines_index');
            $table->dropColumn('can_manage_routines');
        });
    }
};
