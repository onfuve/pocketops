<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tasks')) {
            return;
        }

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE tasks MODIFY taskable_type VARCHAR(255) NULL');
            DB::statement('ALTER TABLE tasks MODIFY taskable_id BIGINT UNSIGNED NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE tasks ALTER COLUMN taskable_type DROP NOT NULL');
            DB::statement('ALTER TABLE tasks ALTER COLUMN taskable_id DROP NOT NULL');
        }
    }

    public function down(): void
    {
        // Leave columns nullable; no down() change to avoid breaking existing data
    }
};
