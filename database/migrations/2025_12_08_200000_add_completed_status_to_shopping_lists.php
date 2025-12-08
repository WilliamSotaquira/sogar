<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if ($this->usingSqlite()) {
            $this->recreateStatusColumnForSqlite(
                ['active', 'closed', 'completed'],
                'status'
            );

            return;
        }

        DB::statement("ALTER TABLE sogar_shopping_lists MODIFY status ENUM('active','closed','completed') DEFAULT 'active'");
    }

    public function down(): void
    {
        if ($this->usingSqlite()) {
            $this->recreateStatusColumnForSqlite(
                ['active', 'closed'],
                "CASE WHEN status = 'completed' THEN 'closed' ELSE status END"
            );

            return;
        }

        DB::statement("UPDATE sogar_shopping_lists SET status = 'closed' WHERE status = 'completed'");
        DB::statement("ALTER TABLE sogar_shopping_lists MODIFY status ENUM('active','closed') DEFAULT 'active'");
    }

    private function usingSqlite(): bool
    {
        return DB::getDriverName() === 'sqlite';
    }

    private function recreateStatusColumnForSqlite(array $allowedValues, string $updateExpression): void
    {
        // SQLite lacks MODIFY ENUM support, so rebuild the column via a temporary field.
        $quotedValues = implode(', ', array_map(
            fn (string $value): string => "'" . $value . "'",
            $allowedValues
        ));

        $temporaryColumn = 'status_tmp';

        DB::statement("ALTER TABLE sogar_shopping_lists ADD COLUMN {$temporaryColumn} TEXT NOT NULL DEFAULT 'active' CHECK ({$temporaryColumn} IN ({$quotedValues}))");
        DB::statement("UPDATE sogar_shopping_lists SET {$temporaryColumn} = {$updateExpression}");
        $this->dropSqliteIndexesReferencingStatus();
        DB::statement('ALTER TABLE sogar_shopping_lists DROP COLUMN status');
        DB::statement("ALTER TABLE sogar_shopping_lists RENAME COLUMN {$temporaryColumn} TO status");
        $this->ensureSqliteIndexesReferencingStatus();
    }

    private function dropSqliteIndexesReferencingStatus(): void
    {
        DB::statement('DROP INDEX IF EXISTS sogar_shopping_lists_family_group_id_status_index');
    }

    private function ensureSqliteIndexesReferencingStatus(): void
    {
        DB::statement('CREATE INDEX IF NOT EXISTS sogar_shopping_lists_family_group_id_status_index ON sogar_shopping_lists (family_group_id, status)');
    }
};
