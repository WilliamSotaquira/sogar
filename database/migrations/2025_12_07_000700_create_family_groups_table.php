<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabla de grupos familiares
        Schema::create('family_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Familia Sotaquirá", etc.
            $table->text('description')->nullable();
            $table->foreignId('admin_user_id')->constrained('users')->onDelete('cascade'); // Administrador del grupo
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tabla pivot para miembros de familia
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_group_id')->constrained('family_groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['padre', 'madre', 'hijo', 'hija', 'otro'])->default('otro');
            $table->boolean('is_admin')->default(false); // Si puede administrar el grupo
            $table->boolean('can_manage_finances')->default(false); // Permisos para finanzas
            $table->boolean('can_manage_food')->default(false); // Permisos para módulo de alimentos
            $table->boolean('can_manage_shopping')->default(false); // Permisos para listas de compra
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            // Un usuario solo puede estar una vez en un grupo familiar
            $table->unique(['family_group_id', 'user_id']);
        });

        // Agregar campo de grupo familiar activo al usuario
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('active_family_group_id')->nullable()->constrained('family_groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['active_family_group_id']);
            $table->dropColumn('active_family_group_id');
        });
        
        Schema::dropIfExists('family_members');
        Schema::dropIfExists('family_groups');
    }
};
