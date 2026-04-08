<?php

namespace App\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Throwable;

class DataBackupService
{
    public const FORMAT_VERSION = 1;

    public const APP_KEY = 'pocket-business';

    /**
     * Laravel / some clients list tables as "database.table" or "schema.table".
     * The app always uses unqualified table names for Schema and the query builder.
     */
    protected function normalizeTableName(string $table): string
    {
        $table = trim($table);
        if ($table === '') {
            return $table;
        }
        if (str_contains($table, '.')) {
            return substr($table, (int) strrpos($table, '.') + 1);
        }

        return $table;
    }

    /**
     * @param  array<string, mixed>  $tablesData
     * @return array<string, mixed>
     */
    protected function normalizeBackupTableKeys(array $tablesData): array
    {
        $out = [];
        foreach ($tablesData as $key => $rows) {
            if (! is_string($key)) {
                continue;
            }
            $bare = $this->normalizeTableName($key);
            if (array_key_exists($bare, $out)) {
                throw new InvalidArgumentException(
                    'فایل پشتیبان برای جدول «'.$bare.'» دو ورودی دارد (کلیدهای با و بدون نام دیتابیس). یکی را در JSON حذف یا ادغام کنید.'
                );
            }
            $out[$bare] = $rows;
        }

        return $out;
    }

    /** @return list<string> */
    protected function excludedTables(): array
    {
        return [
            'migrations',
            'cache',
            'cache_locks',
            'sessions',
            'jobs',
            'job_batches',
            'failed_jobs',
            'password_reset_tokens',
            // SQLite internals — must not be driven by app JSON
            'sqlite_sequence',
            'sqlite_stat1',
        ];
    }

    /**
     * @return list<string>
     */
    public function listBackupTables(): array
    {
        $exclude = array_flip($this->excludedTables());
        $tables = [];
        foreach (Schema::getTableListing() as $name) {
            $name = $this->normalizeTableName($name);
            if ($name === '' || isset($exclude[$name])) {
                continue;
            }
            $tables[] = $name;
        }
        $tables = array_values(array_unique($tables));
        sort($tables);

        return $tables;
    }

    /**
     * Lightweight stats for the settings UI (admin-only).
     *
     * @return array{table_count: int, row_count: int, driver: string}
     */
    public function summarizeForDisplay(): array
    {
        $tables = $this->listBackupTables();
        $rowCount = 0;
        foreach ($tables as $table) {
            $rowCount += (int) DB::table($table)->count();
        }

        return [
            'table_count' => count($tables),
            'row_count' => $rowCount,
            'driver' => Schema::getConnection()->getDriverName(),
        ];
    }

    /**
     * @return array{format_version: int, app: string, exported_at: string, laravel: string, database_driver: string, row_count_total: int, tables: array<string, list<array<string, mixed>>>}
     */
    public function exportPayload(): array
    {
        $tables = $this->listBackupTables();
        $data = [];
        $rowTotal = 0;
        foreach ($tables as $table) {
            $rows = DB::table($table)->get();
            $data[$table] = $rows->map(fn ($row) => $this->rowToArray($row))->all();
            $rowTotal += count($data[$table]);
        }

        return [
            'format_version' => self::FORMAT_VERSION,
            'app' => self::APP_KEY,
            'exported_at' => now()->toIso8601String(),
            'laravel' => app()->version(),
            'database_driver' => Schema::getConnection()->getDriverName(),
            'row_count_total' => $rowTotal,
            'tables' => $data,
        ];
    }

    /**
     * @param  object|array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function rowToArray(object|array $row): array
    {
        $arr = is_array($row) ? $row : get_object_vars($row);
        foreach ($arr as $k => $v) {
            if ($v instanceof \DateTimeInterface) {
                $arr[$k] = $v->format('Y-m-d H:i:s');
            }
        }

        return $arr;
    }

    /**
     * Replace application data from a decoded backup payload (full restore).
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws InvalidArgumentException
     * @throws Throwable
     */
    public function importFromDecodedArray(array $payload): void
    {
        if (($payload['app'] ?? null) !== self::APP_KEY) {
            throw new InvalidArgumentException('این فایل پشتیبان معتبر برنامه pocket-business نیست.');
        }

        $formatVersion = $payload['format_version'] ?? null;
        if ($formatVersion !== self::FORMAT_VERSION && $formatVersion !== (string) self::FORMAT_VERSION) {
            throw new InvalidArgumentException('نسخهٔ قالب این پشتیبان پشتیبانی نمی‌شود. باید نسخهٔ '.self::FORMAT_VERSION.' باشد.');
        }

        $tablesData = $payload['tables'] ?? null;
        if (! is_array($tablesData)) {
            throw new InvalidArgumentException('ساختار فایل پشتیبان ناقص است (جداول یافت نشد).');
        }

        $tablesData = $this->normalizeBackupTableKeys($tablesData);

        $this->assertBackupTablesAreCompatible($tablesData);

        $targetTables = $this->listBackupTables();

        try {
            DB::transaction(function () use ($targetTables, $tablesData) {
                $this->withoutForeignKeyChecks(function () use ($targetTables, $tablesData) {
                    foreach ($targetTables as $table) {
                        DB::table($table)->delete();
                    }
                    foreach ($tablesData as $table => $rows) {
                        if (! is_string($table) || ! Schema::hasTable($table) || in_array($table, $this->excludedTables(), true)) {
                            continue;
                        }
                        if (! is_array($rows) || $rows === []) {
                            continue;
                        }
                        foreach (array_chunk($rows, 200) as $chunk) {
                            $clean = [];
                            foreach ($chunk as $row) {
                                if (! is_array($row)) {
                                    continue;
                                }
                                $clean[] = $this->rowForInsert($table, $row);
                            }
                            if ($clean !== []) {
                                DB::table($table)->insert($clean);
                            }
                        }
                    }
                });
            });
            try {
                $this->refreshSqliteAutoincrement();
            } catch (Throwable $e) {
                Log::warning('SQLite sequence refresh after backup import failed', ['exception' => $e]);
            }
        } catch (QueryException $e) {
            Log::warning('Data backup import failed (SQL)', [
                'exception' => $e,
                'sql' => $e->getSql(),
            ]);
            throw new InvalidArgumentException(
                'بازیابی با خطای دیتابیس متوقف شد. معمولاً یعنی اسکیمای این نصب با فایل پشتیبان یکی نیست (ابتدا همان نسخهٔ migrate را اجرا کنید) یا محدودیت یکتا/کلید خارجی نقض شده است.'
            );
        }
    }

    /**
     * Merge remote backup-shaped payload into the current database (does not truncate all tables).
     *
     * @param  array<string, mixed>  $payload
     * @param  array{add_missing?: bool, update_existing?: bool, delete_orphans?: bool, include_users?: bool}  $options
     * @return array{inserted: int, updated: int, deleted: int}
     */
    public function mergeFromRemotePayload(array $payload, array $options): array
    {
        $addMissing = (bool) ($options['add_missing'] ?? true);
        $updateExisting = (bool) ($options['update_existing'] ?? true);
        $deleteOrphans = (bool) ($options['delete_orphans'] ?? false);
        $includeUsers = (bool) ($options['include_users'] ?? false);

        if (($payload['app'] ?? null) !== self::APP_KEY) {
            throw new InvalidArgumentException('این payload معتبر برنامه pocket-business نیست.');
        }

        $formatVersion = $payload['format_version'] ?? null;
        if ($formatVersion !== self::FORMAT_VERSION && $formatVersion !== (string) self::FORMAT_VERSION) {
            throw new InvalidArgumentException('نسخهٔ قالب داده پشتیبانی نمی‌شود. باید نسخهٔ '.self::FORMAT_VERSION.' باشد.');
        }

        $tablesData = $payload['tables'] ?? null;
        if (! is_array($tablesData)) {
            throw new InvalidArgumentException('ساختار payload ناقص است (جداول یافت نشد).');
        }

        $tablesData = $this->normalizeBackupTableKeys($tablesData);
        $this->assertBackupTablesAreCompatible($tablesData);

        $stats = ['inserted' => 0, 'updated' => 0, 'deleted' => 0];
        $tables = $this->listBackupTables();

        try {
            DB::transaction(function () use ($tables, $tablesData, $addMissing, $updateExisting, $deleteOrphans, $includeUsers, &$stats) {
                $this->withoutForeignKeyChecks(function () use ($tables, $tablesData, $addMissing, $updateExisting, $deleteOrphans, $includeUsers, &$stats) {
                    foreach ($tables as $table) {
                        if ($table === 'users' && ! $includeUsers) {
                            continue;
                        }
                        if (! isset($tablesData[$table]) || ! is_array($tablesData[$table])) {
                            continue;
                        }
                        $rows = $tablesData[$table];

                        $isSettings = $table === 'settings';
                        if ($deleteOrphans) {
                            if ($isSettings) {
                                $remoteKeys = [];
                                foreach ($rows as $r) {
                                    if (is_array($r) && array_key_exists('key', $r) && $r['key'] !== null && $r['key'] !== '') {
                                        $remoteKeys[] = (string) $r['key'];
                                    }
                                }
                                $remoteKeys = array_values(array_unique($remoteKeys));
                                $deleted = $remoteKeys === []
                                    ? DB::table($table)->delete()
                                    : DB::table($table)->whereNotIn('key', $remoteKeys)->delete();
                                $stats['deleted'] += (int) $deleted;
                            } elseif (Schema::hasColumn($table, 'id')) {
                                $remoteIds = [];
                                foreach ($rows as $r) {
                                    if (is_array($r) && isset($r['id']) && $r['id'] !== null && $r['id'] !== '') {
                                        $remoteIds[] = $r['id'];
                                    }
                                }
                                $remoteIds = array_values(array_unique($remoteIds));
                                $deleted = $remoteIds === []
                                    ? DB::table($table)->delete()
                                    : DB::table($table)->whereNotIn('id', $remoteIds)->delete();
                                $stats['deleted'] += (int) $deleted;
                            }
                        }

                        foreach ($rows as $row) {
                            if (! is_array($row)) {
                                continue;
                            }
                            $clean = $this->rowForInsert($table, $row);
                            if ($isSettings) {
                                $key = $clean['key'] ?? null;
                                if ($key === null || $key === '') {
                                    continue;
                                }
                                $exists = DB::table($table)->where('key', $key)->exists();
                                if ($exists) {
                                    if ($updateExisting) {
                                        $update = Arr::except($clean, ['key']);
                                        if ($update !== []) {
                                            DB::table($table)->where('key', $key)->update($update);
                                            $stats['updated']++;
                                        }
                                    }
                                } elseif ($addMissing) {
                                    DB::table($table)->insert($clean);
                                    $stats['inserted']++;
                                }
                            } elseif (Schema::hasColumn($table, 'id')) {
                                $id = $clean['id'] ?? null;
                                if ($id === null || $id === '') {
                                    continue;
                                }
                                $exists = DB::table($table)->where('id', $id)->exists();
                                if ($exists) {
                                    if ($updateExisting) {
                                        $update = Arr::except($clean, ['id']);
                                        if ($update !== []) {
                                            DB::table($table)->where('id', $id)->update($update);
                                            $stats['updated']++;
                                        }
                                    }
                                } elseif ($addMissing) {
                                    DB::table($table)->insert($clean);
                                    $stats['inserted']++;
                                }
                            }
                        }
                    }
                });
            });
            try {
                $this->refreshSqliteAutoincrement();
            } catch (Throwable $e) {
                Log::warning('SQLite sequence refresh after merge sync failed', ['exception' => $e]);
            }
        } catch (QueryException $e) {
            Log::warning('Data merge sync failed (SQL)', [
                'exception' => $e,
                'sql' => $e->getSql(),
            ]);
            throw new InvalidArgumentException(
                'ادغام با خطای دیتابیس متوقف شد. اسکیما را با نصب مقابل هم‌تراز کنید و محدودیت یکتا/کلید خارجی را بررسی کنید.'
            );
        }

        return $stats;
    }

    /**
     * Fail fast if the file targets tables that do not exist (would silently drop data).
     *
     * @param  array<string, mixed>  $tablesData
     */
    protected function assertBackupTablesAreCompatible(array $tablesData): void
    {
        foreach ($tablesData as $table => $rows) {
            if (! is_string($table)) {
                continue;
            }
            if (! is_array($rows) || $rows === []) {
                continue;
            }
            if (in_array($table, $this->excludedTables(), true)) {
                continue;
            }
            if (! Schema::hasTable($table)) {
                throw new InvalidArgumentException(
                    'جدول «'.$table.'» در دیتابیس فعلی وجود ندارد؛ احتمالاً نسخهٔ برنامه یا migrate با محیط پشتیبان‌گیری فرق دارد. ابتدا کد و migrationها را هم‌تراز کنید، سپس دوباره تلاش کنید.'
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function rowForInsert(string $table, array $row): array
    {
        $columns = Schema::getColumnListing($table);
        $out = [];
        foreach ($columns as $col) {
            $out[$col] = array_key_exists($col, $row) ? $row[$col] : null;
        }

        return $out;
    }

    protected function withoutForeignKeyChecks(callable $callback): mixed
    {
        $conn = Schema::getConnection();
        $driver = $conn->getDriverName();

        if ($driver === 'mysql') {
            $conn->statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'sqlite') {
            $conn->statement('PRAGMA foreign_keys = OFF');
        } elseif ($driver === 'pgsql') {
            $conn->statement("SET session_replication_role = 'replica'");
        }

        try {
            return $callback();
        } finally {
            if ($driver === 'mysql') {
                $conn->statement('SET FOREIGN_KEY_CHECKS=1');
            } elseif ($driver === 'sqlite') {
                $conn->statement('PRAGMA foreign_keys = ON');
            } elseif ($driver === 'pgsql') {
                $conn->statement("SET session_replication_role = 'origin'");
            }
        }
    }

    protected function refreshSqliteAutoincrement(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            return;
        }
        if (! Schema::hasTable('sqlite_sequence')) {
            return;
        }
        foreach ($this->listBackupTables() as $table) {
            if (! Schema::hasColumn($table, 'id')) {
                continue;
            }
            $maxId = DB::table($table)->max('id');
            if ($maxId === null) {
                DB::table('sqlite_sequence')->where('name', $table)->delete();

                continue;
            }
            DB::table('sqlite_sequence')->updateOrInsert(
                ['name' => $table],
                ['seq' => $maxId]
            );
        }
    }
}
