<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sogar_routines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_group_id')
                ->nullable()
                ->constrained('family_groups')
                ->nullOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['family_group_id', 'is_active']);
        });

        Schema::create('sogar_routine_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_id')->constrained('sogar_routines')->cascadeOnDelete();

            $table->string('title');
            $table->string('group', 32)->default('Personal'); // Hogar|Familiar|Trabajo|Personal|Salud ...
            $table->string('category', 64)->nullable();

            $table->time('start_time');
            $table->time('end_time');

            // 7-bit mask (Mon=1 .. Sun=64). Ej: día hábil = 1+2+4+8+16
            $table->unsignedTinyInteger('weekdays_mask')->default(31);

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['routine_id', 'is_active']);
            $table->index(['weekdays_mask']);
            $table->index(['start_time', 'end_time']);
        });

        Schema::create('sogar_routine_item_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_item_id')->constrained('sogar_routine_items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->date('occurred_on');
            $table->string('status', 16)->default('done'); // done|skipped
            $table->dateTime('occurred_at')->nullable();
            $table->string('note')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['routine_item_id', 'user_id', 'occurred_on'], 'sogar_routine_item_logs_unique');
            $table->index(['user_id', 'occurred_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sogar_routine_item_logs');
        Schema::dropIfExists('sogar_routine_items');
        Schema::dropIfExists('sogar_routines');
    }
};
