<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 100)->nullable()->after('name');
            $table->string('last_name', 100)->nullable()->after('first_name');
            $table->date('date_of_birth')->nullable()->after('age');
            $table->string('blood_group', 8)->nullable()->after('date_of_birth');
            $table->string('helper_status', 20)->nullable()->after('is_active');
            $table->boolean('on_duty')->default(false)->after('helper_status');
            $table->string('address', 255)->nullable()->after('village_id');
            $table->string('pincode', 20)->nullable()->after('address');
            $table->string('ration_card_no', 50)->nullable()->after('pincode');
            $table->string('avatar_url', 500)->nullable()->after('ration_card_no');
            $table->boolean('blood_donor_active')->default(true)->after('avatar_url');
            $table->boolean('sms_alerts_active')->default(true)->after('blood_donor_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'date_of_birth',
                'blood_group',
                'helper_status',
                'on_duty',
                'address',
                'pincode',
                'ration_card_no',
                'avatar_url',
                'blood_donor_active',
                'sms_alerts_active',
            ]);
        });
    }
};
