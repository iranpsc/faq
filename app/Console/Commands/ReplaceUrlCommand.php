<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReplaceUrlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'url:replace
                            {--dry-run : Preview changes without executing them}
                            {--rollback : Rollback the last URL replacement operation}
                            {--batch-size=100 : Number of records to process in each batch}
                            {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replace "https://faq.irpsc.com/wp-content/" with "https://api.faqhub.ir/storage/" in database content fields';

    /**
     * The tables and fields to process
     */
    private array $tablesToProcess = [
        'questions' => ['content'],
        'answers' => ['content'],
        'comments' => ['content'],
    ];

    /**
     * The old URL pattern to replace
     */
    private string $oldUrlPattern = 'https://faq.irpsc.com/wp-content/';

    /**
     * The new URL pattern to replace with
     */
    private string $newUrlPattern = 'https://api.faqhub.ir/storage/';

    /**
     * Backup table name for rollback functionality
     */
    private string $backupTableName = 'url_replacement_backup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('rollback')) {
            return $this->rollbackOperation();
        }

        if ($this->option('dry-run')) {
            return $this->performDryRun();
        }

        return $this->performReplacement();
    }

    /**
     * Perform a dry run to preview changes
     */
    private function performDryRun(): int
    {
        $this->info('🔍 Performing dry run - previewing changes...');
        $this->newLine();

        $totalAffected = 0;
        $totalRecords = 0;

        foreach ($this->tablesToProcess as $table => $fields) {
            $this->info("📊 Analyzing table: {$table}");

            foreach ($fields as $field) {
                $count = $this->countRecordsWithPattern($table, $field);
                $totalRecords += $count;

                if ($count > 0) {
                    $this->line("  - Field '{$field}': {$count} records contain the URL pattern");
                    $totalAffected += $count;
                }
            }
        }

        $this->newLine();
        $this->info("📈 Summary:");
        $this->line("  - Total records to be updated: {$totalAffected}");
        $this->line("  - Total records scanned: {$totalRecords}");

        if ($totalAffected === 0) {
            $this->info('✅ No records found with the URL pattern. Nothing to replace.');
            return 0;
        }

        $this->newLine();
        $this->warn('⚠️  This is a DRY RUN. No changes will be made.');
        $this->info('Run without --dry-run to perform the actual replacement.');

        return 0;
    }

    /**
     * Perform the actual URL replacement
     */
    private function performReplacement(): int
    {
        $this->info('🚀 Starting URL replacement operation...');
        $this->newLine();

        // Create backup table if it doesn't exist
        $this->createBackupTable();

        // Count total affected records first
        $totalAffected = $this->countTotalAffectedRecords();

        if ($totalAffected === 0) {
            $this->info('✅ No records found with the URL pattern. Nothing to replace.');
            return 0;
        }

        $this->info("📊 Found {$totalAffected} records to update");
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm("Do you want to proceed with replacing URLs in {$totalAffected} records?")) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $batchSize = (int) $this->option('batch-size');
        $processedRecords = 0;
        $startTime = microtime(true);

        // Start database transaction
        DB::beginTransaction();

        try {
            foreach ($this->tablesToProcess as $table => $fields) {
                $this->info("🔄 Processing table: {$table}");

                foreach ($fields as $field) {
                    $processed = $this->processTableField($table, $field, $batchSize);
                    $processedRecords += $processed;

                    if ($processed > 0) {
                        $this->line("  ✅ Updated {$processed} records in field '{$field}'");
                    }
                }
            }

            // Log the operation
            $this->logOperation($processedRecords, 'replace');

            DB::commit();

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $this->newLine();
            $this->info("✅ URL replacement completed successfully!");
            $this->line("  - Records processed: {$processedRecords}");
            $this->line("  - Duration: {$duration} seconds");
            $this->line("  - Backup created for rollback functionality");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();

            $this->error("❌ Error during URL replacement: " . $e->getMessage());
            Log::error('URL replacement failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }

    /**
     * Process a specific table field
     */
    private function processTableField(string $table, string $field, int $batchSize): int
    {
        $processed = 0;
        $offset = 0;

        while (true) {
            // Get records that contain the pattern
            $records = DB::table($table)
                ->where($field, 'LIKE', '%' . $this->oldUrlPattern . '%')
                ->select('id', $field)
                ->limit($batchSize)
                ->offset($offset)
                ->get();

            if ($records->isEmpty()) {
                break;
            }

            foreach ($records as $record) {
                $originalContent = $record->$field;
                $newContent = str_replace($this->oldUrlPattern, $this->newUrlPattern, $originalContent);

                // Only update if content actually changed
                if ($originalContent !== $newContent) {
                    // Create backup entry
                    $this->createBackupEntry($table, $record->id, $field, $originalContent);

                    // Update the record
                    DB::table($table)
                        ->where('id', $record->id)
                        ->update([$field => $newContent]);

                    $processed++;
                }
            }

            $offset += $batchSize;
        }

        return $processed;
    }

    /**
     * Count records containing the URL pattern
     */
    private function countRecordsWithPattern(string $table, string $field): int
    {
        return DB::table($table)
            ->where($field, 'LIKE', '%' . $this->oldUrlPattern . '%')
            ->count();
    }

    /**
     * Count total affected records across all tables
     */
    private function countTotalAffectedRecords(): int
    {
        $total = 0;

        foreach ($this->tablesToProcess as $table => $fields) {
            foreach ($fields as $field) {
                $total += $this->countRecordsWithPattern($table, $field);
            }
        }

        return $total;
    }

    /**
     * Create backup table for rollback functionality
     */
    private function createBackupTable(): void
    {
        if (!DB::getSchemaBuilder()->hasTable($this->backupTableName)) {
            DB::statement("
                CREATE TABLE {$this->backupTableName} (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    table_name VARCHAR(255) NOT NULL,
                    record_id BIGINT UNSIGNED NOT NULL,
                    field_name VARCHAR(255) NOT NULL,
                    original_content LONGTEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_table_record (table_name, record_id),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
    }

    /**
     * Create backup entry for rollback
     */
    private function createBackupEntry(string $table, int $recordId, string $field, string $originalContent): void
    {
        DB::table($this->backupTableName)->insert([
            'table_name' => $table,
            'record_id' => $recordId,
            'field_name' => $field,
            'original_content' => $originalContent,
            'created_at' => Carbon::now()
        ]);
    }

    /**
     * Log the operation
     */
    private function logOperation(int $processedRecords, string $operation): void
    {
        Log::info("URL replacement operation completed", [
            'operation' => $operation,
            'processed_records' => $processedRecords,
            'old_pattern' => $this->oldUrlPattern,
            'new_pattern' => $this->newUrlPattern,
            'timestamp' => Carbon::now()->toISOString()
        ]);
    }

    /**
     * Rollback the last URL replacement operation
     */
    private function rollbackOperation(): int
    {
        $this->info('🔄 Starting rollback operation...');
        $this->newLine();

        // Check if backup table exists
        if (!DB::getSchemaBuilder()->hasTable($this->backupTableName)) {
            $this->error('❌ No backup table found. Cannot perform rollback.');
            return 1;
        }

        // Get the latest backup entries
        $latestBackup = DB::table($this->backupTableName)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latestBackup) {
            $this->error('❌ No backup entries found. Nothing to rollback.');
            return 1;
        }

        $backupCount = DB::table($this->backupTableName)
            ->where('created_at', $latestBackup->created_at)
            ->count();

        $this->info("📊 Found {$backupCount} records to rollback from {$latestBackup->created_at}");

        if (!$this->option('force')) {
            if (!$this->confirm("Do you want to rollback {$backupCount} records?")) {
                $this->info('Rollback cancelled.');
                return 0;
            }
        }

        $batchSize = (int) $this->option('batch-size');
        $processedRecords = 0;
        $startTime = microtime(true);

        // Start database transaction
        DB::beginTransaction();

        try {
            $offset = 0;

            while (true) {
                $backupEntries = DB::table($this->backupTableName)
                    ->where('created_at', $latestBackup->created_at)
                    ->limit($batchSize)
                    ->offset($offset)
                    ->get();

                if ($backupEntries->isEmpty()) {
                    break;
                }

                foreach ($backupEntries as $entry) {
                    // Restore original content
                    DB::table($entry->table_name)
                        ->where('id', $entry->record_id)
                        ->update([$entry->field_name => $entry->original_content]);

                    $processedRecords++;
                }

                $offset += $batchSize;
            }

            // Remove the backup entries after successful rollback
            DB::table($this->backupTableName)
                ->where('created_at', $latestBackup->created_at)
                ->delete();

            // Log the rollback operation
            $this->logOperation($processedRecords, 'rollback');

            DB::commit();

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $this->newLine();
            $this->info("✅ Rollback completed successfully!");
            $this->line("  - Records restored: {$processedRecords}");
            $this->line("  - Duration: {$duration} seconds");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();

            $this->error("❌ Error during rollback: " . $e->getMessage());
            Log::error('URL replacement rollback failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }
}
