<?php

namespace Database\Seeders;

use App\Models\LaborLawRule;
use Illuminate\Database\Seeder;

class LaborLawRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [
            // التأخير 0-15 دقيقة
            [
                'violation_type' => 'late_0_15',
                'min_minutes' => 0,
                'max_minutes' => 14,
                'repeat_number' => 1,
                'action_type' => 'warning',
                'action_value' => null,
                'reason_text' => 'تأخير عن موعد العمل لمدة تتراوح بين 0 و 15 دقيقة - المرة الأولى: إنذار كتابي وفقاً لنظام العمل السعودي',
            ],
            [
                'violation_type' => 'late_0_15',
                'min_minutes' => 0,
                'max_minutes' => 14,
                'repeat_number' => 2,
                'action_type' => 'deduction_percentage',
                'action_value' => 5,
                'reason_text' => 'تأخير عن موعد العمل لمدة تتراوح بين 0 و 15 دقيقة - المرة الثانية: خصم 5% من الأجر اليومي وفقاً لنظام العمل السعودي',
            ],
            [
                'violation_type' => 'late_0_15',
                'min_minutes' => 0,
                'max_minutes' => 14,
                'repeat_number' => 3,
                'action_type' => 'deduction_percentage',
                'action_value' => 10,
                'reason_text' => 'تأخير عن موعد العمل لمدة تتراوح بين 0 و 15 دقيقة - المرة الثالثة: خصم 10% من الأجر اليومي وفقاً لنظام العمل السعودي',
            ],
            [
                'violation_type' => 'late_0_15',
                'min_minutes' => 0,
                'max_minutes' => 14,
                'repeat_number' => 4,
                'action_type' => 'deduction_percentage',
                'action_value' => 20,
                'reason_text' => 'تأخير عن موعد العمل لمدة تتراوح بين 0 و 15 دقيقة - المرة الرابعة: خصم 20% من الأجر اليومي وفقاً لنظام العمل السعودي',
            ],

            // التأخير 15-30 دقيقة
            [
                'violation_type' => 'late_15_30',
                'min_minutes' => 15,
                'max_minutes' => 29,
                'repeat_number' => 1,
                'action_type' => 'warning',
                'action_value' => null,
                'reason_text' => 'تأخير عن موعد العمل لمدة تتراوح بين 15 و 30 دقيقة - المرة الأولى: إنذار كتابي وفقاً لنظام العمل السعودي',
            ],
            [
                'violation_type' => 'late_15_30',
                'min_minutes' => 15,
                'max_minutes' => 29,
                'repeat_number' => 2,
                'action_type' => 'deduction_percentage',
                'action_value' => 15,
                'reason_text' => 'تأخير عن موعد العمل لمدة تتراوح بين 15 و 30 دقيقة - المرة الثانية: خصم 15% من الأجر اليومي وفقاً لنظام العمل السعودي',
            ],
            [
                'violation_type' => 'late_15_30',
                'min_minutes' => 15,
                'max_minutes' => 29,
                'repeat_number' => 3,
                'action_type' => 'deduction_percentage',
                'action_value' => 25,
                'reason_text' => 'تأخير عن موعد العمل لمدة تتراوح بين 15 و 30 دقيقة - المرة الثالثة: خصم 25% من الأجر اليومي وفقاً لنظام العمل السعودي',
            ],
            [
                'violation_type' => 'late_15_30',
                'min_minutes' => 15,
                'max_minutes' => 29,
                'repeat_number' => 4,
                'action_type' => 'deduction_percentage',
                'action_value' => 50,
                'reason_text' => 'تأخير عن موعد العمل لمدة تتراوح بين 15 و 30 دقيقة - المرة الرابعة: خصم 50% من الأجر اليومي وفقاً لنظام العمل السعودي',
            ],

            // التأخير 30-60 دقيقة
            [
                'violation_type' => 'late_30_60',
                'min_minutes' => 30,
                'max_minutes' => 59,
                'repeat_number' => 1,
                'action_type' => 'warning',
                'action_value' => null,
                'reason_text' => 'تأخير عن موعد العمل لمدة تتراوح بين 30 و 60 دقيقة - المرة الأولى: إنذار كتابي وفقاً لنظام العمل السعودي',
            ],
            [
                'violation_type' => 'late_30_60',
                'min_minutes' => 30,
                'max_minutes' => 59,
                'repeat_number' => 2,
                'action_type' => 'deduction_percentage',
                'action_value' => 20,
                'reason_text' => 'تأخير عن موعد العمل لمدة تتراوح بين 30 و 60 دقيقة - المرة الثانية: خصم 20% من الأجر اليومي وفقاً لنظام العمل السعودي',
            ],
            [
                'violation_type' => 'late_30_60',
                'min_minutes' => 30,
                'max_minutes' => 59,
                'repeat_number' => 3,
                'action_type' => 'deduction_percentage',
                'action_value' => 30,
                'reason_text' => 'تأخير عن موعد العمل لمدة تتراوح بين 30 و 60 دقيقة - المرة الثالثة: خصم 30% من الأجر اليومي وفقاً لنظام العمل السعودي',
            ],
            [
                'violation_type' => 'late_30_60',
                'min_minutes' => 30,
                'max_minutes' => 59,
                'repeat_number' => 4,
                'action_type' => 'deduction_days',
                'action_value' => 1,
                'reason_text' => 'تأخير عن موعد العمل لمدة تتراوح بين 30 و 60 دقيقة - المرة الرابعة: خصم أجر يوم كامل وفقاً لنظام العمل السعودي',
            ],

            // التأخير أكثر من 60 دقيقة
            [
                'violation_type' => 'late_over_60',
                'min_minutes' => 60,
                'max_minutes' => null,
                'repeat_number' => 1,
                'action_type' => 'warning',
                'action_value' => null,
                'reason_text' => 'تأخير عن موعد العمل لأكثر من 60 دقيقة - المرة الأولى: إنذار كتابي وفقاً لنظام العمل السعودي',
            ],
            [
                'violation_type' => 'late_over_60',
                'min_minutes' => 60,
                'max_minutes' => null,
                'repeat_number' => 2,
                'action_type' => 'deduction_percentage',
                'action_value' => 30,
                'reason_text' => 'تأخير عن موعد العمل لأكثر من 60 دقيقة - المرة الثانية: خصم 30% من الأجر اليومي وفقاً لنظام العمل السعودي',
            ],
            [
                'violation_type' => 'late_over_60',
                'min_minutes' => 60,
                'max_minutes' => null,
                'repeat_number' => 3,
                'action_type' => 'deduction_days',
                'action_value' => 1,
                'reason_text' => 'تأخير عن موعد العمل لأكثر من 60 دقيقة - المرة الثالثة: خصم أجر يوم كامل وفقاً لنظام العمل السعودي',
            ],
            [
                'violation_type' => 'late_over_60',
                'min_minutes' => 60,
                'max_minutes' => null,
                'repeat_number' => 4,
                'action_type' => 'termination',
                'action_value' => null,
                'reason_text' => 'تأخير عن موعد العمل لأكثر من 60 دقيقة - المرة الرابعة: إنهاء الخدمة وفقاً لنظام العمل السعودي',
            ],
        ];

        foreach ($rules as $rule) {
            LaborLawRule::create($rule);
        }
    }
}
