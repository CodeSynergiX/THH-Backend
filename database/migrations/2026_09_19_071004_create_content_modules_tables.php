<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schemes', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->boolean('is_published')->default(true);
            $table->json('eligibility_rules')->nullable();
            $table->json('benefits')->nullable();
            $table->json('required_documents')->nullable();
            $table->json('process_steps')->nullable();
            $table->timestamps();
        });

        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->decimal('amount', 10, 2)->nullable();
            $table->json('eligibility_rules')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('company');
            $table->string('location');
            $table->string('salary_range')->nullable();
            $table->json('requirements')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('libraries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('taluka_id')->nullable()->constrained('talukas')->nullOnDelete();
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->text('address');
            $table->string('contact_person')->nullable();
            $table->string('phone', 20)->nullable();
            $table->unsignedInteger('total_books')->default(0);
            $table->unsignedInteger('computers_count')->default(0);
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('mock_tests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category', 50);
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->unsignedSmallInteger('total_marks')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('mock_test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mock_test_id')->constrained('mock_tests')->cascadeOnDelete();
            $table->text('question_text');
            $table->json('options');
            $table->string('correct_answer');
            $table->text('explanation')->nullable();
            $table->timestamps();
        });

        Schema::create('study_materials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category', 50);
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->string('file_type', 20)->default('pdf');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('mentor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->json('expertise')->nullable();
            $table->text('bio')->nullable();
            $table->string('designation')->nullable();
            $table->string('organization')->nullable();
            $table->boolean('is_available')->default(true);
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->timestamps();
        });

        Schema::create('mentor_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('question');
            $table->text('answer')->nullable();
            $table->string('status', 20)->default('pending'); // pending, answered
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('business_ideas', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category', 50);
            $table->string('investment_range', 50)->nullable();
            $table->text('description');
            $table->text('market_potential')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('health_camps', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('organizer')->nullable();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->text('address');
            $table->timestamp('scheduled_at');
            $table->json('doctors_specialties')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->text('address');
            $table->string('phone', 20)->nullable();
            $table->string('emergency_contact', 20)->nullable();
            $table->boolean('has_blood_bank')->default(false);
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('blood_requests', function (Blueprint $table) {
            $table->id();
            $table->string('patient_name');
            $table->string('blood_group', 10);
            $table->foreignId('hospital_id')->nullable()->constrained('hospitals')->nullOnDelete();
            $table->unsignedSmallInteger('units_required')->default(1);
            $table->string('urgency', 20)->default('urgent');
            $table->string('status', 20)->default('active'); // active, fulfilled, cancelled
            $table->string('contact_phone', 20);
            $table->timestamps();
        });

        Schema::create('sakhi_circles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->string('leader_name');
            $table->string('leader_phone', 20);
            $table->unsignedSmallInteger('members_count')->default(10);
            $table->json('activities')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sakhi_circles');
        Schema::dropIfExists('blood_requests');
        Schema::dropIfExists('hospitals');
        Schema::dropIfExists('health_camps');
        Schema::dropIfExists('business_ideas');
        Schema::dropIfExists('mentor_questions');
        Schema::dropIfExists('mentor_profiles');
        Schema::dropIfExists('study_materials');
        Schema::dropIfExists('mock_test_questions');
        Schema::dropIfExists('mock_tests');
        Schema::dropIfExists('libraries');
        Schema::dropIfExists('job_postings');
        Schema::dropIfExists('scholarships');
        Schema::dropIfExists('schemes');
    }
};
