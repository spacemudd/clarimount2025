<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ZkDevice;
use App\Models\ZkAttendanceRaw;
use App\Models\ZkDailyAttendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Service for ingesting ZKTeco attendance log data from /iclock/cdata endpoint
 * 
 * Timezone Handling:
 * - Incoming timestamps are assumed to be in Asia/Riyadh timezone
 * - All timestamps are converted to UTC before storage (Laravel best practice)
 */
class ZkAttlogIngestService
{
    /**
     * Ingest attendance log data from ZKTeco device
     *
     * @param string $serialNumber Device serial number from query parameter SN
     * @param string $rawBody Raw request body containing TAB-separated lines
     * @param \DateTime $receivedAt Timestamp when the request was received
     * @return void
     */
    public function ingest(string $serialNumber, string $rawBody, \DateTime $receivedAt): void
    {
        // Get or create device
        $device = ZkDevice::firstOrCreate(
            ['serial_number' => $serialNumber],
            ['name' => null]
        );

        // Update device last_seen_at
        $device->update(['last_seen_at' => $receivedAt]);

        // Split body into lines and filter empty lines
        $lines = array_filter(
            explode("\n", $rawBody),
            fn($line) => trim($line) !== ''
        );

        // Process each line
        foreach ($lines as $line) {
            try {
                $this->processLine($device, trim($line), $receivedAt);
            } catch (\Exception $e) {
                // Log warning but continue processing other lines
                Log::channel('daily')->warning('[ZKTeco] Error processing attendance line', [
                    'device_id' => $device->id,
                    'serial_number' => $serialNumber,
                    'line' => $line,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Process a single attendance log line
     *
     * @param ZkDevice $device
     * @param string $line TAB-separated line
     * @param \DateTime $receivedAt
     * @return void
     */
    private function processLine(ZkDevice $device, string $line, \DateTime $receivedAt): void
    {
        // Split by TAB
        $columns = explode("\t", $line);

        // Validate minimum columns required
        if (count($columns) < 2) {
            throw new \InvalidArgumentException('Line does not have enough columns');
        }

        // Extract data
        $devicePin = trim($columns[0]);
        $punchTimeStr = trim($columns[1]);
        $verifyMode = isset($columns[3]) && $columns[3] !== '' ? (int)$columns[3] : null;

        // Parse punch_time from Asia/Riyadh timezone and convert to UTC
        // Format: "2026-01-18 15:31:30"
        $punchTime = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $punchTimeStr,
            'Asia/Riyadh'
        )->utc();

        // Validate device_pin
        if (empty($devicePin)) {
            throw new \InvalidArgumentException('Device PIN is empty');
        }

        // Get date from punch_time for daily attendance
        $attDate = $punchTime->copy()->setTimezone('Asia/Riyadh')->format('Y-m-d');

        // Use database transaction for atomicity
        DB::transaction(function () use ($device, $devicePin, $punchTime, $verifyMode, $line, $receivedAt, $attDate) {
            // Try to create raw attendance record (will skip if duplicate due to unique constraint)
            try {
                ZkAttendanceRaw::firstOrCreate(
                    [
                        'device_id' => $device->id,
                        'device_pin' => $devicePin,
                        'punch_time' => $punchTime,
                    ],
                    [
                        'verify_mode' => $verifyMode,
                        'raw_line' => $line,
                        'received_at' => Carbon::instance($receivedAt),
                    ]
                );
            } catch (\Illuminate\Database\QueryException $e) {
                // If duplicate (unique constraint violation), skip silently
                // This is expected behavior to prevent duplicate records
                if ($e->getCode() === '23000') {
                    return;
                }
                throw $e;
            }

            // Update or create daily attendance record
            $dailyAttendance = ZkDailyAttendance::firstOrNew([
                'device_id' => $device->id,
                'device_pin' => $devicePin,
                'att_date' => $attDate,
            ]);

            if ($dailyAttendance->exists) {
                // Update existing record
                // Update first_punch if this is earlier
                if ($punchTime->lt($dailyAttendance->first_punch)) {
                    $dailyAttendance->first_punch = $punchTime;
                    $dailyAttendance->first_verify_mode = $verifyMode;
                }

                // Update last_punch if this is later
                if ($punchTime->gt($dailyAttendance->last_punch)) {
                    $dailyAttendance->last_punch = $punchTime;
                    $dailyAttendance->last_verify_mode = $verifyMode;
                }

                // Increment punch count
                $dailyAttendance->punch_count = ($dailyAttendance->punch_count ?? 0) + 1;
            } else {
                // Create new record
                $dailyAttendance->first_punch = $punchTime;
                $dailyAttendance->last_punch = $punchTime;
                $dailyAttendance->first_verify_mode = $verifyMode;
                $dailyAttendance->last_verify_mode = $verifyMode;
                $dailyAttendance->punch_count = 1;
            }

            $dailyAttendance->save();
        });
    }
}
