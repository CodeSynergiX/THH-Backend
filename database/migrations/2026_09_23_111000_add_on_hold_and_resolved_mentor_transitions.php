<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $transitions = [
            ['assigned', 'onHold', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['assigned', 'resolved', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['assigned', 'rejected', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['assistance', 'onHold', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['assistance', 'resolved', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['assistance', 'rejected', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['onHold', 'assistance', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], false],
            ['onHold', 'assigned', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], false],
            ['onHold', 'awaiting_confirmation', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['onHold', 'resolved', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['needMoreInfo', 'onHold', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['followUp', 'onHold', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['verification', 'onHold', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['verification', 'rejected', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
            ['verification', 'resolved', ['staff', 'mentor', 'volunteer', 'admin', 'super_admin'], true],
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

    public function down(): void {}
};
