<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->unique()->after('name');
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->string('gender', 20)->nullable()->after('password');
            $table->unsignedTinyInteger('age')->nullable()->after('gender');
            $table->foreignId('district_id')->after('age')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('taluka_id')->after('district_id')->nullable()->constrained('talukas')->nullOnDelete();
            $table->foreignId('village_id')->after('taluka_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->string('occupation')->nullable()->after('village_id');
            $table->string('education')->nullable()->after('occupation');
            $table->string('income_category', 50)->nullable()->after('education');
            $table->string('community', 100)->nullable()->after('income_category');
            $table->string('locale', 10)->default('gu')->after('community');
            $table->string('theme_preference', 20)->default('system')->after('locale');
            $table->string('fcm_status', 30)->default('disabled')->after('theme_preference');
            $table->boolean('is_active')->default(true)->after('fcm_status');
            $table->timestamp('consent_at')->nullable()->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('consent_at');
            $table->softDeletes()->after('updated_at');
        });

        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index();
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'phone',
                'gender',
                'age',
                'district_id',
                'taluka_id',
                'village_id',
                'occupation',
                'education',
                'income_category',
                'community',
                'locale',
                'theme_preference',
                'fcm_status',
                'is_active',
                'consent_at',
                'last_login_at',
            ]);
        });
    }
};
