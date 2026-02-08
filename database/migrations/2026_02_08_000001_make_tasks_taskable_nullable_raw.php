<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE tasks MODIFY taskable_type VARCHAR(255) NULL');
            DB::statement('ALTER TABLE tasks MODIFY taskable_id BIGINT UNSIGNED NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE tasks ALTER COLUMN taskable_type DROP NOT NULL');
            DB::statement('ALTER TABLE tasks ALTER COLUMN taskable_id DROP NOT NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't support MODIFY; nullable is usually already applied when migration ran
            // If needed we could recreate table - skip for now as SQLite often allows null by default
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE tasks MODIFY taskable_type VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE tasks MODIFY taskable_id BIGINT UNSIGNED NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE tasks ALTER COLUMN taskable_type SET NOT NULL');
            DB::statement('ALTER TABLE tasks ALTER COLUMN taskable_id SET NOT NULL');
        }
    }
};
