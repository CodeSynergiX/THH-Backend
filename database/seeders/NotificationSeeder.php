<?php

namespace Database\Seeders;

use App\Domains\Notifications\Models\NotificationTemplate;
use App\Domains\Settings\Models\Setting;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Global notification channels settings
        $settings = [
            ['key' => 'email_notifications_enabled', 'value' => true, 'group' => 'notifications'],
            ['key' => 'push_notifications_enabled',  'value' => true, 'group' => 'notifications'],
            ['key' => 'smtp_host',                   'value' => 'smtp.mailtrap.io', 'group' => 'smtp'],
            ['key' => 'smtp_port',                   'value' => 587, 'group' => 'smtp'],
            ['key' => 'smtp_username',               'value' => 'thh_demo_smtp', 'group' => 'smtp'],
            ['key' => 'smtp_from_name',              'value' => 'Tribal Helping Hand (GGVT)', 'group' => 'smtp'],
            ['key' => 'smtp_from_email',             'value' => 'noreply@ggvt.org', 'group' => 'smtp'],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(
                ['key' => $s['key']],
                ['value' => $s['value'], 'group' => $s['group']]
            );
        }

        // Notification templates with English & Gujarati translations
        $templates = [
            [
                'event_key' => 'case_submitted',
                'channel' => 'all',
                'title_template' => [
                    'en' => 'Request Received: {case_no}',
                    'gu' => 'અરજી પ્રાપ્ત થઈ: {case_no}',
                ],
                'body_template' => [
                    'en' => 'Your help request {case_no} for "{title}" has been registered. Our team will verify it shortly.',
                    'gu' => 'તમારી સહાય અરજી {case_no} "{title}" નોંધાઈ ગઈ છે. અમારી ટીમ ટૂંક સમયમાં તેની ચકાસણી કરશે.',
                ],
                'is_enabled' => true,
            ],
            [
                'event_key' => 'case_status_verification',
                'channel' => 'all',
                'title_template' => [
                    'en' => 'Under Verification: {case_no}',
                    'gu' => 'ચકાસણી હેઠળ: {case_no}',
                ],
                'body_template' => [
                    'en' => 'Your case {case_no} is currently being verified by our field coordinator.',
                    'gu' => 'તમારો કેસ {case_no} હાલમાં અમારા ફીલ્ડ સંયોજક દ્વારા ચકાસવામાં આવી રહ્યો છે.',
                ],
                'is_enabled' => true,
            ],
            [
                'event_key' => 'case_status_categorised',
                'channel' => 'all',
                'title_template' => [
                    'en' => 'Case Categorised: {case_no}',
                    'gu' => 'કેસ વર્ગીકૃત થયો: {case_no}',
                ],
                'body_template' => [
                    'en' => 'Your case {case_no} has been categorized for specialized assistance.',
                    'gu' => 'તમારો કેસ {case_no} વિશેષ સહાય માટે વર્ગીકૃત કરવામાં આવ્યો છે.',
                ],
                'is_enabled' => true,
            ],
            [
                'event_key' => 'case_status_assigned',
                'channel' => 'all',
                'title_template' => [
                    'en' => 'Officer Assigned: {case_no}',
                    'gu' => 'અધિકારી સોંપાયા: {case_no}',
                ],
                'body_template' => [
                    'en' => 'A dedicated mentor or field officer has been assigned to your case {case_no}.',
                    'gu' => 'તમારા કેસ {case_no} માટે સમર્પિત માર્ગદર્શક અથવા ફીલ્ડ ઓફિસર સોંપવામાં આવ્યા છે.',
                ],
                'is_enabled' => true,
            ],
            [
                'event_key' => 'case_status_assistance',
                'channel' => 'all',
                'title_template' => [
                    'en' => 'Assistance In Progress: {case_no}',
                    'gu' => 'સહાય પ્રક્રિયા ચાલુ છે: {case_no}',
                ],
                'body_template' => [
                    'en' => 'Assistance is actively being provided for case {case_no}. {note}',
                    'gu' => 'કેસ {case_no} માટે સક્રિય રીતે સહાય પૂરી પાડવામાં આવી રહી છે. {note}',
                ],
                'is_enabled' => true,
            ],
            [
                'event_key' => 'case_status_follow_up',
                'channel' => 'all',
                'title_template' => [
                    'en' => 'Follow-up Scheduled: {case_no}',
                    'gu' => 'ફોલો-અપ સુનિશ્ચિત: {case_no}',
                ],
                'body_template' => [
                    'en' => 'A follow-up review has been scheduled for case {case_no}.',
                    'gu' => 'કેસ {case_no} માટે ફોલો-અપ સમીક્ષા સુનિશ્ચિત કરવામાં આવી છે.',
                ],
                'is_enabled' => true,
            ],
            [
                'event_key' => 'case_status_resolved',
                'channel' => 'all',
                'title_template' => [
                    'en' => 'Case Resolved Successfully: {case_no}',
                    'gu' => 'કેસ સફળતાપૂર્વક ઉકેલાયો: {case_no}',
                ],
                'body_template' => [
                    'en' => 'Great news! Your request {case_no} has been resolved. Thank you for reaching out to GGVT.',
                    'gu' => 'સારા સમાચાર! તમારી વિનંતી {case_no} નો ઉકેલ આવ્યો છે. GGVT નો સંપર્ક કરવા બદલ આભાર.',
                ],
                'is_enabled' => true,
            ],
            [
                'event_key' => 'case_status_need_more_info',
                'channel' => 'all',
                'title_template' => [
                    'en' => 'More Information Needed: {case_no}',
                    'gu' => 'વધુ માહિતીની જરૂર છે: {case_no}',
                ],
                'body_template' => [
                    'en' => 'Please provide additional details for your request {case_no}. {note}',
                    'gu' => 'કૃપા કરીને તમારી અરજી {case_no} માટે વધારાની વિગતો આપો. {note}',
                ],
                'is_enabled' => true,
            ],
            [
                'event_key' => 'case_status_rejected',
                'channel' => 'all',
                'title_template' => [
                    'en' => 'Case Status Update: {case_no}',
                    'gu' => 'કેસ સ્થિતિ અપડેટ: {case_no}',
                ],
                'body_template' => [
                    'en' => 'Your case {case_no} could not be processed at this time. Reason: {note}',
                    'gu' => 'તમારો કેસ {case_no} આ સમયે પ્રક્રિયા કરી શકાયો નથી. કારણ: {note}',
                ],
                'is_enabled' => true,
            ],
            [
                'event_key' => 'case_status_on_hold',
                'channel' => 'all',
                'title_template' => [
                    'en' => 'Case On Hold: {case_no}',
                    'gu' => 'કેસ મોકૂફ રાખેલ છે: {case_no}',
                ],
                'body_template' => [
                    'en' => 'Case {case_no} is temporarily on hold: {note}',
                    'gu' => 'કેસ {case_no} અસ્થાયી રૂપે મોકૂફ રાખવામાં આવ્યો છે: {note}',
                ],
                'is_enabled' => true,
            ],
            [
                'event_key' => 'case_assigned',
                'channel' => 'all',
                'title_template' => [
                    'en' => 'Case assigned to you: {case_no}',
                    'gu' => 'કેસ તમને સોંપાયો: {case_no}',
                ],
                'body_template' => [
                    'en' => 'You have been assigned {case_no} — {title}. Please confirm and proceed.',
                    'gu' => 'તમને કેસ {case_no} — {title} સોંપાયો છે. પુષ્ટિ કરો અને આગળ વધો.',
                ],
                'is_enabled' => true,
            ],
            [
                'event_key' => 'case_status_awaiting_confirmation',
                'channel' => 'all',
                'title_template' => [
                    'en' => 'Please confirm your case is resolved: {case_no}',
                    'gu' => 'કેસ ઉકેલાયો છે તે પુષ્ટિ કરો: {case_no}',
                ],
                'body_template' => [
                    'en' => 'The field desk believes {case_no} is complete. Confirm to close, or tell us if help is still needed.',
                    'gu' => 'ક્ષેત્ર ડેસ્ક માને છે કે {case_no} પૂરું થયું. બંધ કરવા પુષ્ટિ કરો, અથવા હજુ સહાય જોઈએ તો કહો.',
                ],
                'is_enabled' => true,
            ],
            [
                'event_key' => 'case_status_reopened',
                'channel' => 'all',
                'title_template' => [
                    'en' => 'Case Reopened: {case_no}',
                    'gu' => 'કેસ ફરીથી ખોલવામાં આવ્યો: {case_no}',
                ],
                'body_template' => [
                    'en' => 'Case {case_no} has been reopened for further assistance.',
                    'gu' => 'વધુ સહાય માટે કેસ {case_no} ફરીથી ખોલવામાં આવ્યો છે.',
                ],
                'is_enabled' => true,
            ],
            [
                'event_key' => 'sla_breach',
                'channel' => 'all',
                'title_template' => [
                    'en' => 'Attention: SLA Breach on {case_no}',
                    'gu' => 'ધ્યાન આપો: {case_no} પર SLA સમય મર્યાદા સમાપ્ત',
                ],
                'body_template' => [
                    'en' => 'Case {case_no} ({title}) has breached its target resolution timeline. Immediate action required.',
                    'gu' => 'કેસ {case_no} ({title}) એ તેની સમય મર્યાદા વટાવી દીધી છે. તાત્કાલિક કાર્યવાહી જરૂરી છે.',
                ],
                'is_enabled' => true,
            ],
        ];

        foreach ($templates as $t) {
            NotificationTemplate::updateOrCreate(
                ['event_key' => $t['event_key']],
                $t
            );
        }
    }
}
