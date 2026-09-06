<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeFileAccessLog;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeFileAccessLogService
{
    public function record(Employee $employee, ?User $user, ?Request $request = null): void
    {
        if ($user === null) {
            return;
        }

        $request ??= request();

        EmployeeFileAccessLog::query()->create([
            'employee_id' => $employee->id,
            'user_id' => $user->id,
            'ip_address' => $request?->ip(),
            'user_agent' => $this->truncate($request?->userAgent(), 1023),
            'url' => $this->truncate($request?->fullUrl(), 2048),
            'created_at' => now(),
        ]);
    }

    private function truncate(?string $value, int $max): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return mb_strlen($value) > $max ? mb_substr($value, 0, $max) : $value;
    }
}
