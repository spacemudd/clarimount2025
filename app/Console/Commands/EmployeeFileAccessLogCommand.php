<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmployeeFileAccessLog;
use Illuminate\Console\Command;

class EmployeeFileAccessLogCommand extends Command
{
    protected $signature = 'employees:file-access-log
                            {employee : Employee database id or employee_id code}
                            {--limit=50 : Max rows to show}
                            {--from= : From date (Y-m-d)}
                            {--to= : To date (Y-m-d)}';

    protected $description = 'List who opened an employee profile page (terminal only; not shown in the UI)';

    public function handle(): int
    {
        $employee = $this->resolveEmployee((string) $this->argument('employee'));

        if ($employee === null) {
            $this->error('Employee not found.');

            return self::FAILURE;
        }

        $limit = max(1, (int) $this->option('limit'));
        $from = $this->option('from');
        $to = $this->option('to');

        $query = EmployeeFileAccessLog::query()
            ->where('employee_id', $employee->id)
            ->with('user:id,name,email')
            ->latest('id');

        if (is_string($from) && $from !== '') {
            $query->whereDate('created_at', '>=', $from);
        }

        if (is_string($to) && $to !== '') {
            $query->whereDate('created_at', '<=', $to);
        }

        $rows = $query->limit($limit)->get();

        $this->info(sprintf(
            'Employee #%d (%s) — last %d access log(s)',
            $employee->id,
            trim($employee->full_name) !== '' ? $employee->full_name : ($employee->employee_id ?? 'n/a'),
            $rows->count(),
        ));

        if ($rows->isEmpty()) {
            $this->warn('No file access logs found.');

            return self::SUCCESS;
        }

        $this->table(
            ['When', 'User', 'Email', 'IP', 'URL'],
            $rows->map(static fn (EmployeeFileAccessLog $log): array => [
                $log->created_at?->timezone('Asia/Riyadh')->format('Y-m-d H:i:s') ?? '',
                $log->user?->name ?? 'deleted/unknown',
                $log->user?->email ?? '',
                $log->ip_address ?? '',
                $log->url ?? '',
            ])->all(),
        );

        return self::SUCCESS;
    }

    private function resolveEmployee(string $identifier): ?Employee
    {
        if (ctype_digit($identifier)) {
            $byId = Employee::query()->find((int) $identifier);
            if ($byId !== null) {
                return $byId;
            }
        }

        return Employee::query()
            ->where('employee_id', $identifier)
            ->first();
    }
}
