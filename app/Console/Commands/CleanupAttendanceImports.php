<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AttendanceImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupAttendanceImports extends Command
{
    protected $signature = 'attendance:cleanup-imports 
                           {--days=30 : Delete imports older than this many days}
                           {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Clean up old attendance import files and records';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("Looking for attendance imports older than {$days} days...");

        $cutoffDate = now()->subDays($days);

        $oldImports = AttendanceImport::where('created_at', '<', $cutoffDate)
            ->with('records')
            ->get();

        if ($oldImports->isEmpty()) {
            $this->info('No old imports found to clean up.');
            return 0;
        }

        $this->info("Found {$oldImports->count()} old imports to clean up.");

        if ($dryRun) {
            $this->table(
                ['ID', 'Filename', 'Created At', 'Records Count', 'File Size'],
                $oldImports->map(function ($import) {
                    $fileSize = 'N/A';
                    if (Storage::disk('local')->exists($import->file_path)) {
                        $fileSize = $this->formatBytes(Storage::disk('local')->size($import->file_path));
                    }

                    return [
                        $import->id,
                        $import->filename,
                        $import->created_at->format('Y-m-d H:i:s'),
                        $import->records->count(),
                        $fileSize,
                    ];
                })->toArray()
            );

            $this->info('Dry run completed. Use without --dry-run to actually delete.');
            return 0;
        }

        $deletedFiles = 0;
        $deletedImports = 0;
        $totalRecords = 0;

        foreach ($oldImports as $import) {
            try {
                // Delete the file if it exists
                if (Storage::disk('local')->exists($import->file_path)) {
                    Storage::disk('local')->delete($import->file_path);
                    $deletedFiles++;
                }

                $recordCount = $import->records->count();
                $totalRecords += $recordCount;

                // Delete the import (cascade will delete records and sync batches)
                $import->delete();
                $deletedImports++;

                $this->line("Deleted import {$import->id}: {$import->filename} ({$recordCount} records)");

            } catch (\Exception $e) {
                $this->error("Failed to delete import {$import->id}: {$e->getMessage()}");
            }
        }

        $this->info("Cleanup completed:");
        $this->line("- Deleted {$deletedImports} imports");
        $this->line("- Deleted {$deletedFiles} files");
        $this->line("- Removed {$totalRecords} attendance records");

        return 0;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
