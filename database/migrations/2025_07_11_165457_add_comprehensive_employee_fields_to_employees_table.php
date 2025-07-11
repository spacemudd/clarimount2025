<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // General Information Section
            $table->string('father_name')->nullable()->after('first_name');
            $table->string('nationality')->nullable()->after('last_name');
            $table->string('residence_country')->nullable()->after('nationality');
            $table->date('birth_date')->nullable()->after('residence_country');
            $table->string('personal_email')->nullable()->after('email');
            $table->string('work_email')->nullable()->after('personal_email');
            
            // Work Details Section
            $table->date('employment_date')->nullable()->after('hire_date');
            $table->date('probation_end_date')->nullable()->after('employment_date');
            $table->string('work_phone')->nullable()->after('phone');
            $table->string('fingerprint_device_id')->nullable()->after('mobile');
            $table->string('work_address')->nullable()->after('fingerprint_device_id');
            
            // Legal Information Section
            $table->string('id_number')->nullable()->after('work_address');
            $table->date('residence_expiry_date')->nullable()->after('id_number');
            $table->date('contract_end_date')->nullable()->after('residence_expiry_date');
            $table->date('exit_reentry_visa_expiry')->nullable()->after('contract_end_date');
            $table->string('passport_number')->nullable()->after('exit_reentry_visa_expiry');
            $table->date('passport_expiry_date')->nullable()->after('passport_number');
            
            // Insurance Section
            $table->string('insurance_policy')->nullable()->after('passport_expiry_date');
            $table->date('insurance_expiry_date')->nullable()->after('insurance_policy');
            
            // Employment Status Section
            $table->date('departure_date')->nullable()->after('termination_date');
            $table->string('departure_reason')->nullable()->after('departure_date');
            
            // Managers/Workflow Section
            $table->string('direct_manager')->nullable()->after('manager');
            $table->string('additional_approver_2')->nullable()->after('direct_manager');
            $table->string('additional_approver_3')->nullable()->after('additional_approver_2');
            
            // Emergency Contact Section
            $table->string('emergency_contact_name')->nullable()->after('additional_approver_3');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_email')->nullable()->after('emergency_contact_phone');
            $table->text('emergency_contact_address')->nullable()->after('emergency_contact_email');
            
            // Additional indexes for frequently searched fields
            $table->index(['nationality']);
            $table->index(['employment_status', 'employment_date']);
            $table->index(['id_number']);
            $table->index(['fingerprint_device_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['nationality']);
            $table->dropIndex(['employment_status', 'employment_date']);
            $table->dropIndex(['id_number']);
            $table->dropIndex(['fingerprint_device_id']);
            
            // Drop all added columns
            $table->dropColumn([
                'father_name',
                'nationality',
                'residence_country',
                'birth_date',
                'personal_email',
                'work_email',
                'employment_date',
                'probation_end_date',
                'work_phone',
                'fingerprint_device_id',
                'work_address',
                'id_number',
                'residence_expiry_date',
                'contract_end_date',
                'exit_reentry_visa_expiry',
                'passport_number',
                'passport_expiry_date',
                'insurance_policy',
                'insurance_expiry_date',
                'departure_date',
                'departure_reason',
                'direct_manager',
                'additional_approver_2',
                'additional_approver_3',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_email',
                'emergency_contact_address',
            ]);
        });
    }
};
