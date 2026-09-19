<?php

namespace Database\Seeders;

use App\Domains\Settings\Models\Language;
use App\Domains\Settings\Models\ThemeVersion;
use App\Domains\Settings\Models\Translation;
use Illuminate\Database\Seeder;

class ThemeAndLocalizationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Initial Dignified NGO Theme
        $lightTokens = [
            'primary' => '#B33925',     // Warm Terracotta
            'secondary' => '#2D5A3D',   // Deep Forest Green
            'accent' => '#D48822',      // Warm Ochre
            'bg' => '#FDFBF7',          // Warm Parchment
            'surface' => '#FFFFFF',
            'text' => '#1F2937',        // Deep Charcoal (11.5:1 contrast on #FDFBF7)
            'text_muted' => '#6B7280',  // 4.6:1 contrast
            'border' => '#E5E7EB',
            'status_received' => '#2563EB',
            'status_verification' => '#7C3AED',
            'status_categorised' => '#4F46E5',
            'status_assigned' => '#DB2777',
            'status_assistance' => '#D97706',
            'status_followup' => '#059669',
            'status_resolved' => '#10B981',
            'status_rejected' => '#DC2626',
            'status_onhold' => '#6B7280',
        ];

        $darkTokens = [
            'primary' => '#E05A44',
            'secondary' => '#4E8B62',
            'accent' => '#E5A13B',
            'bg' => '#121212',
            'surface' => '#1E1E1E',
            'text' => '#F3F4F6',        // 15:1 contrast on #121212
            'text_muted' => '#9CA3AF',  // 7.5:1 contrast
            'border' => '#2E2E2E',
            'status_received' => '#60A5FA',
            'status_verification' => '#A78BFA',
            'status_categorised' => '#818CF8',
            'status_assigned' => '#F472B6',
            'status_assistance' => '#FBBF24',
            'status_followup' => '#34D399',
            'status_resolved' => '#34D399',
            'status_rejected' => '#F87171',
            'status_onhold' => '#9CA3AF',
        ];

        ThemeVersion::firstOrCreate(
            ['version' => 1],
            [
                'light' => $lightTokens,
                'dark' => $darkTokens,
                'meta' => [
                    'name' => 'GGVT Dignified Heritage',
                    'wcag_contrast_light' => '11.5:1 (AAA)',
                    'wcag_contrast_dark' => '15:1 (AAA)',
                ],
                'is_published' => true,
                'published_at' => now(),
                'notes' => 'Initial dignified NGO theme palette for Tribal Helping Hand.',
            ]
        );

        // 2. Languages
        Language::firstOrCreate(
            ['code' => 'gu'],
            [
                'name' => 'Gujarati',
                'native_name' => 'ગુજરાતી',
                'is_default' => true,
                'is_enabled' => true,
                'direction' => 'ltr',
            ]
        );

        Language::firstOrCreate(
            ['code' => 'en'],
            [
                'name' => 'English',
                'native_name' => 'English',
                'is_default' => false,
                'is_enabled' => true,
                'direction' => 'ltr',
            ]
        );

        // 3. Comprehensive Translations
        $translations = [
            // APP GROUP
            ['app', 'portal.title', 'આદિવાસી સહાયક હાથ', 'Tribal Helping Hand'],
            ['app', 'portal.tagline', 'ગ્લોબલ ગ્રામીણ વિકાસ ટ્રસ્ટ (GGVT) દ્વારા આદિવાસી સશક્તિકરણ પહેલ', 'Tribal Empowerment Initiative by Global Gramin Vikas Trust'],
            ['app', 'portal.track_case_heading', 'તમારી અરજીની સ્થિતિ તપાસો', 'Track Your Help Request'],
            ['app', 'portal.track_case_placeholder', 'દા.ત. THH-2026-00001', 'e.g. THH-2026-00001'],
            ['app', 'portal.track_button', 'શોધો', 'Track Now'],
            ['app', 'portal.request_help_cta', 'નવી સહાય અરજી કરો', 'Submit Help Request'],
            ['app', 'portal.request_help_sub', 'શિક્ષણ, આરોગ્ય, યોજનાઓ અને ગામ સમસ્યાઓ માટે વિનામૂલ્યે સહાય', 'Free assistance for education, healthcare, schemes & village support'],
            ['app', 'portal.services_heading', 'અમારી મુખ્ય સેવાઓ', 'Our Core Support Pillars'],
            ['app', 'portal.stat_citizens_helped', 'સહાયિત નાગરિકો', 'Citizens Supported'],
            ['app', 'portal.stat_cases_resolved', 'ઉકેલાયેલ પ્રશ્નો', 'Cases Resolved'],
            ['app', 'portal.stat_villages_covered', 'આવરી લીધેલા ગામો', 'Villages Covered'],
            ['app', 'portal.staff_login', 'કર્મચારી પ્રવેશ', 'Staff Login'],
            ['app', 'portal.admin_login', 'એડમિન પ્રવેશ', 'Admin Login'],
            ['app', 'portal.emergency_contact', 'ઇમરજન્સી હેલ્પલાઇન', 'Emergency Helpline'],
            ['app', 'portal.empty_records', 'કોઈ માહિતી ઉપલબ્ધ નથી', 'No records available'],
            ['app', 'portal.not_applicable', '-', '-'],
            ['app', 'common.loading', 'લોડ થઈ રહ્યું છે...', 'Loading...'],
            ['app', 'common.search', 'શોધો...', 'Search...'],
            ['app', 'common.filter', 'ફિલ્ટર', 'Filter'],
            ['app', 'common.save', 'સાચવો', 'Save'],
            ['app', 'common.cancel', 'રદ કરો', 'Cancel'],
            ['app', 'common.back', 'પાછા જાઓ', 'Back'],
            ['app', 'common.status', 'સ્થિતિ', 'Status'],
            ['app', 'common.actions', 'ક્રિયાઓ', 'Actions'],
            ['app', 'common.details', 'વિગતો', 'Details'],

            // AUTH GROUP
            ['auth', 'login.heading_title', 'પ્રવેશ કરો', 'Login to Portal'],
            ['auth', 'login.phone_label', 'મોબાઇલ નંબર', 'Mobile Number'],
            ['auth', 'login.send_otp_button', 'OTP મેળવો', 'Request OTP'],
            ['auth', 'login.otp_label', 'દાખલ કરો OTP', 'Enter OTP'],
            ['auth', 'login.verify_otp_button', 'વેરિફાય કરો', 'Verify & Proceed'],
            ['auth', 'login.admin_email_label', 'ઇમેઇલ સરનામું', 'Email Address'],
            ['auth', 'login.password_label', 'પાસવર્ડ', 'Password'],
            ['auth', 'login.admin_login_button', 'એડમિન લૉગિન', 'Admin Sign In'],
            ['auth', 'logout.button', 'બહાર નીકળો', 'Sign Out'],
            ['auth', 'otp.sent_success', 'તમારા મોબાઇલ પર OTP મોકલવામાં આવ્યો છે.', 'OTP has been sent to your mobile phone.'],
            ['auth', 'otp.resend_button', 'ફરીથી OTP મોકલો', 'Resend OTP'],

            // CASES GROUP
            ['cases', 'status.received', 'પ્રાપ્ત થયેલ', 'Received'],
            ['cases', 'status.verification', 'ચકાસણી હેઠળ', 'Verification'],
            ['cases', 'status.categorised', 'વર્ગીકૃત', 'Categorised'],
            ['cases', 'status.assigned', 'સોંપાયેલ', 'Assigned'],
            ['cases', 'status.assistance', 'સહાય કાર્યરત', 'Assistance in Progress'],
            ['cases', 'status.followup', 'ફોલો-અપ', 'Follow-up'],
            ['cases', 'status.resolved', 'સફળ ઉકેલ', 'Resolved'],
            ['cases', 'status.rejected', 'અસ્વીકાર્ય', 'Rejected'],
            ['cases', 'status.onhold', 'સ્થગિત', 'On Hold'],
            ['cases', 'status.needmoreinfo', 'વધુ માહિતી જરૂરી', 'Need More Info'],
            ['cases', 'status.reopened', 'પુનઃ ખોલેલ', 'Reopened'],
            ['cases', 'detail.case_no_label', 'અરજી ક્રમાંક', 'Case Number'],
            ['cases', 'detail.citizen_label', 'અરજદારનું નામ', 'Citizen Name'],
            ['cases', 'detail.village_label', 'ગામ', 'Village'],
            ['cases', 'detail.timeline_heading', 'સમયરેખા પ્રગતિ', 'Timeline Progress'],
            ['cases', 'detail.documents_heading', 'જોડાયેલા દસ્તાવેજો', 'Attached Documents'],
            ['cases', 'form.category_label', 'કેટેગરી પસંદ કરો', 'Select Category'],
            ['cases', 'form.description_label', 'વિગતવાર રજૂઆત', 'Detailed Description'],
            ['cases', 'form.submit_button', 'અરજી મોકલો', 'Submit Application'],
            ['cases', 'form.urgency_label', 'તાકીદ', 'Urgency'],
            ['cases', 'form.priority_label', 'પ્રાથમિકતા', 'Priority'],

            // THEME GROUP
            ['theme', 'editor.title', 'થીમ અને દેખાવ વ્યવસ્થાપક', 'Theme & Appearance Manager'],
            ['theme', 'editor.subtitle', 'સંપૂર્ણ પ્લેટફોર્મના રંગો અને બ્રાન્ડ ઓળખનું જીવંત નિયંત્રણ', 'Live control over platform colors, contrast compliance and brand identity'],
            ['theme', 'editor.light_tab', 'લાઇટ મોડ રંગો', 'Light Mode Palette'],
            ['theme', 'editor.dark_tab', 'ડાર્ક મોડ રંગો', 'Dark Mode Palette'],
            ['theme', 'editor.auto_dark_button', 'ઓટો-ડાર્ક મોડ જનરેટ કરો', 'Auto-Generate Dark Mode'],
            ['theme', 'editor.publish_button', 'આવૃત્તિ પ્રકાશિત કરો', 'Publish Live Theme'],
            ['theme', 'editor.save_draft_button', 'ડ્રાફ્ટ સાચવો', 'Save Draft'],
            ['theme', 'editor.rollback_button', 'પાછલી આવૃત્તિ પર જાઓ', 'Rollback Version'],
            ['theme', 'editor.contrast_check_title', 'WCAG કોન્ટ્રાસ્ટ તપાસ', 'WCAG Contrast Compliance'],
            ['theme', 'editor.preview_heading', 'લાઇવ પૂર્વાવલોકન', 'Live Component Preview'],
            ['theme', 'editor.history_heading', 'થીમ આવૃત્તિ ઇતિહાસ', 'Version History'],

            // LOCALIZATION GROUP
            ['localization', 'editor.title', 'ભાષા અને અનુવાદ વ્યવસ્થાપક', 'Localization & Translations'],
            ['localization', 'editor.subtitle', 'ગુજરાતી અને અંગ્રેજી ભાષાનું ડેટાબેઝ-આધારિત સંચાલન', 'Database-driven management for Gujarati and English translations'],
            ['localization', 'editor.scan_missing_button', 'ખૂટતી કી શોધો', 'Scan Missing Keys'],
            ['localization', 'editor.export_json_button', 'JSON નિકાસ', 'Export JSON'],
            ['localization', 'editor.export_csv_button', 'CSV નિકાસ', 'Export CSV'],
            ['localization', 'editor.import_button', 'આયાત કરો', 'Import Translations'],
            ['localization', 'editor.search_placeholder', 'કી અથવા શબ્દ શોધો...', 'Search by key or text...'],
            ['localization', 'editor.group_all', 'બધા ગ્રુપ', 'All Groups'],
            ['localization', 'editor.filter_group', 'ગ્રુપ ફિલ્ટર', 'Filter by Group'],

            // CONTENT MODULES GROUP
            ['content', 'modules.education', 'શિક્ષણ અને છાત્રવૃત્તિ', 'Education & Scholarships'],
            ['content', 'modules.schemes', 'સરકારી યોજનાઓ', 'Government Schemes'],
            ['content', 'modules.jobs', 'રોજગાર સહાય', 'Employment Opportunities'],
            ['content', 'modules.health', 'આરોગ્ય અને દવાખાના', 'Health Camps & Hospitals'],
            ['content', 'modules.blood', 'ઇમરજન્સી રક્ત સહાય', 'Emergency Blood Support'],
            ['content', 'modules.mentorship', 'નિષ્ણાત માર્ગદર્શન', 'Mentor Guidance'],
            ['content', 'modules.sakhi', 'સખી મંડળ પ્રવૃત્તિ', 'Sakhi Circle'],
            ['content', 'modules.village_reports', 'ગામ પ્રશ્નો અને ઉકેલ', 'Village Problems & Solutions'],
        ];

        foreach ($translations as [$group, $key, $valGu, $valEn]) {
            Translation::updateOrCreate(
                ['group' => $group, 'key' => $key, 'locale' => 'gu'],
                ['value' => $valGu, 'needs_review' => false]
            );

            Translation::updateOrCreate(
                ['group' => $group, 'key' => $key, 'locale' => 'en'],
                ['value' => $valEn, 'needs_review' => false]
            );
        }
    }
}
