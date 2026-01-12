<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sogar_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_group_id')
                ->nullable()
                ->constrained('family_groups')
                ->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('kind', 16)->default('habit'); // habit|task
            $table->string('cadence', 16)->default('daily'); // daily|weekly|monthly|once
            $table->unsignedSmallInteger('target_count')->default(1);
            $table->string('unit', 32)->nullable();

            $table->date('start_on')->nullable();
            $table->date('end_on')->nullable();
            $table->date('due_on')->nullable();

            $table->boolean('is_active')->default(true);

            $table->nullableMorphs('subject');
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['family_group_id', 'is_active']);
            $table->index(['kind', 'cadence']);
        });

        Schema::create('sogar_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('sogar_activities')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->date('occurred_on');
            $table->dateTime('occurred_at')->nullable();
            $table->decimal('qty', 12, 3)->default(1);
            $table->string('note')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['activity_id', 'occurred_on']);
            $table->index(['user_id', 'occurred_on']);
        });

        Schema::create('sogar_activity_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('sogar_activities')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_group_id')
                ->nullable()
                ->constrained('family_groups')
                ->nullOnDelete();

            $table->string('goal_type', 16)->default('count'); // count|streak
            $table->unsignedInteger('target_value');
            $table->string('period', 16)->nullable(); // week|month|year (para goal_type=count)

            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['activity_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sogar_activity_goals');
        Schema::dropIfExists('sogar_activity_logs');
        Schema::dropIfExists('sogar_activities');
    }
};
