<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $transitions = [
            ['assigned', 'awaiting_confirmation', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['assigned', 'needMoreInfo', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['assigned', 'followUp', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], false],
            ['assigned', 'verification', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], false],
            ['assigned', 'assistance', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin', 'collector'], false],
            ['assistance', 'awaiting_confirmation', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['assistance', 'needMoreInfo', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['assistance', 'followUp', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], false],
            ['assistance', 'verification', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], false],
            ['assistance', 'assigned', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], false],
            ['needMoreInfo', 'assistance', ['citizen', 'staff', 'mentor', 'volunteer', 'admin', 'super_admin'], false],
            ['needMoreInfo', 'assigned', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], false],
            ['needMoreInfo', 'awaiting_confirmation', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['needMoreInfo', 'verification', ['citizen', 'staff', 'mentor', 'volunteer', 'admin', 'super_admin'], false],
            ['followUp', 'assistance', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], false],
            ['followUp', 'needMoreInfo', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['followUp', 'awaiting_confirmation', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['followUp', 'resolved', ['staff', 'admin', 'super_admin'], true],
            ['verification', 'awaiting_confirmation', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['verification', 'assistance', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], false],
            ['verification', 'assigned', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin', 'collector'], false],
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
        // Safe to leave transitions intact
    }
};
