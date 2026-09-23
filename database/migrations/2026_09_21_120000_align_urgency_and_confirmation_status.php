<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('applications')->where('urgency', 'normal')->update(['urgency' => 'medium']);
        DB::table('applications')->whereIn('urgency', ['critical', 'emergency'])->update(['urgency' => 'urgent']);

        if (Schema::hasTable('blood_requests')) {
            DB::table('blood_requests')->where('urgency', 'normal')->update(['urgency' => 'medium']);
            DB::table('blood_requests')->whereIn('urgency', ['critical', 'emergency'])->update(['urgency' => 'urgent']);
        }

        $transitions = [
            ['assistance', 'awaiting_confirmation', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['followUp', 'awaiting_confirmation', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['awaiting_confirmation', 'resolved', ['citizen', 'staff', 'admin', 'super_admin'], false],
            ['awaiting_confirmation', 'reopened', ['citizen', 'staff', 'admin', 'super_admin'], true],
        ];

        foreach ($transitions as [$from, $to, $roles, $requiresNote]) {
            $existing = DB::table('workflow_transitions')
                ->where('from_status', $from)
                ->where('to_status', $to)
                ->first();

            $payload = [
                'allowed_roles' => json_encode($roles),
                'requires_note' => $requiresNote,
                'requires_documents' => false,
                'updated_at' => now(),
            ];

            if ($existing) {
                DB::table('workflow_transitions')->where('id', $existing->id)->update($payload);
            } else {
                DB::table('workflow_transitions')->insert($payload + [
                    'from_status' => $from,
                    'to_status' => $to,
                    'created_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('applications')->where('urgency', 'medium')->update(['urgency' => 'normal']);
    }
};
