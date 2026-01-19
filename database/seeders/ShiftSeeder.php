<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Shift;
use App\Models\ShiftWorkday;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Weekday representation: 0=Sunday, 1=Monday, 2=Tuesday, 3=Wednesday, 4=Thursday, 5=Friday, 6=Saturday
     */
    public function run(): void
    {
        // Shift 1: السبت-الخميس 9-6
        $shift1 = Shift::create([
            'name' => 'السبت-الخميس 9-6',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'grace_minutes' => 10,
        ]);

        // Workdays: Saturday(6), Sunday(0), Monday(1), Tuesday(2), Wednesday(3), Thursday(4)
        $shift1Workdays = [6, 0, 1, 2, 3, 4];
        foreach ($shift1Workdays as $weekday) {
            ShiftWorkday::create([
                'shift_id' => $shift1->id,
                'weekday' => $weekday,
                'is_workday' => true,
            ]);
        }

        // Shift 2: الأحد-الخميس 9-6
        $shift2 = Shift::create([
            'name' => 'الأحد-الخميس 9-6',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'grace_minutes' => 10,
        ]);

        // Workdays: Sunday(0), Monday(1), Tuesday(2), Wednesday(3), Thursday(4)
        $shift2Workdays = [0, 1, 2, 3, 4];
        foreach ($shift2Workdays as $weekday) {
            ShiftWorkday::create([
                'shift_id' => $shift2->id,
                'weekday' => $weekday,
                'is_workday' => true,
            ]);
        }
    }
}
