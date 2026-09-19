<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_support_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            $table->string('title');
            $table->string('beneficiary_name');
            $table->text('description');
            $table->decimal('amount_required', 10, 2);
            $table->decimal('amount_funded', 10, 2)->default(0);
            $table->string('status', 20)->default('active'); // active, funded, closed
            $table->timestamps();
        });

        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('family_support_cases')->cascadeOnDelete();
            $table->foreignId('donor_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('gateway_ref')->nullable();
            $table->string('status', 20)->default('pending'); // pending, success, failed
            $table->timestamps();
        });

        Schema::create('village_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained('villages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('category', 50)->default('infrastructure'); // road, water, electricity, school, sanitation
            $table->text('description');
            $table->json('photos')->nullable();
            $table->string('status', 30)->default('pending'); // pending, investigating, action_taken, resolved, rejected
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamps();
        });

        Schema::create('plantations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('village_id')->constrained('villages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tree_type', 100);
            $table->string('photo_path')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('status', 20)->default('planted'); // planted, healthy, damaged, deceased
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->json('skills')->nullable();
            $table->string('availability', 50)->default('weekends');
            $table->unsignedInteger('hours_contributed')->default(0);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('organization_name');
            $table->timestamp('mou_signed_at')->nullable();
            $table->unsignedInteger('sponsored_cases_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
        Schema::dropIfExists('volunteers');
        Schema::dropIfExists('plantations');
        Schema::dropIfExists('village_reports');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('family_support_cases');
    }
};
