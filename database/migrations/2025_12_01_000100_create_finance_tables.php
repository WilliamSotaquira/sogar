<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sogar_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['income', 'expense'])->default('expense');
            $table->string('description')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sogar_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('sogar_categories')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->unsignedTinyInteger('month')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->boolean('is_flexible')->default(false);
            $table->boolean('sync_to_calendar')->default(false);
            $table->string('provider_event_id')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sogar_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('initial_balance', 12, 2)->default(0);
            $table->decimal('target_amount', 12, 2)->nullable();
            $table->boolean('is_shared')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sogar_recurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('sogar_categories')->cascadeOnDelete();
            $table->foreignId('wallet_id')->nullable()->constrained('sogar_wallets')->nullOnDelete();
            $table->string('name');
            $table->decimal('amount', 12, 2);
            $table->string('frequency'); // daily|weekly|monthly|yearly|cron-like
            $table->date('next_run_on')->nullable();
            $table->dateTime('last_run_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('sync_to_calendar')->default(false);
            $table->string('provider_event_id')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('sogar_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('sogar_categories')->cascadeOnDelete();
            $table->foreignId('wallet_id')->nullable()->constrained('sogar_wallets')->nullOnDelete();
            $table->foreignId('recurrence_id')->nullable()->constrained('sogar_recurrences')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('occurred_on');
            $table->string('note')->nullable();
            $table->string('origin')->default('manual'); // manual|recurrence
            $table->string('tags')->nullable();
            $table->timestamps();
        });

        Schema::create('sogar_wallet_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('sogar_wallets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('sogar_categories')->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('sogar_transactions')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('occurred_on');
            $table->string('concept')->nullable();
            $table->timestamps();
        });

        Schema::create('sogar_category_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('sogar_categories')->cascadeOnDelete();
            $table->string('keyword');
            $table->timestamps();
            $table->unique(['user_id', 'keyword']);
        });

        Schema::create('sogar_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('provider_user_id')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('scopes')->nullable();
            $table->string('calendar_id')->nullable();
            $table->string('status')->default('active');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sogar_integrations');
        Schema::dropIfExists('sogar_category_keywords');
        Schema::dropIfExists('sogar_recurrences');
        Schema::dropIfExists('sogar_wallet_movements');
        Schema::dropIfExists('sogar_transactions');
        Schema::dropIfExists('sogar_wallets');
        Schema::dropIfExists('sogar_budgets');
        Schema::dropIfExists('sogar_categories');
    }
};
