<?php

namespace Database\Seeders;

use App\Domains\Content\Models\BloodRequest;
use App\Domains\Content\Models\ContentItem;
use App\Domains\Content\Models\ContentModule;
use App\Domains\Content\Models\HealthCamp;
use App\Domains\Content\Models\JobPosting;
use App\Domains\Content\Models\MentorProfile;
use App\Domains\Content\Models\MockTest;
use App\Domains\Content\Models\SakhiCircle;
use App\Domains\Content\Models\Scheme;
use App\Domains\Content\Models\Scholarship;
use App\Domains\Content\Models\VillageReport;
use App\Domains\Settings\Models\StaticPage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentRegistrySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedModules();
        $this->migrateTypedRecords();
        $this->seedStaticPages();
        $this->seedGuidanceItems();
        $this->seedHomeBlocks();
    }

    private function seedModules(): void
    {
        $modules = [
            [
                'slug' => 'schemes',
                'title_en' => 'Government Schemes',
                'title_gu' => 'સરકારી યોજનાઓ',
                'description_en' => 'Awas, Ayushman, Kisan Sahay and Forest Rights. A coordinator sits with the family, checks which papers are ready, and writes the application so the village does not lose a visit to the taluka office.',
                'description_gu' => 'આવાસ, આયુષ્માન, કિસાન સહાય અને જંગલ હક. સંયોજક પરિવાર સાથે બેસીને કાગળ તપાસે છે અને તાલુકા કચેરીની મુલાકાત ન વેડફાય તે રીતે અરજી લખે છે.',
                'icon' => 'landmark',
                'accent_color' => '#7C3AED',
                'sort_order' => 10,
                'type' => 'cms',
            ],
            [
                'slug' => 'scholarships',
                'title_en' => 'Scholarships',
                'title_gu' => 'છાત્રવૃત્તિ સહાય',
                'description_en' => 'Post-matric and college grants, hostel notes and book support. We help the student list what the portal asks for, and we keep a copy of the case so a missed date can be caught.',
                'description_gu' => 'પોસ્ટ-મેટ્રિક અને કોલેજ ગ્રાન્ટ, હોસ્ટેલ નોંધ અને પુસ્તક સહાય. પોર્ટલે માગેલી વિગતો યાદીમાં મૂકીએ છીએ અને છૂટી તારીખ પકડાય તે કેસની નકલ રાખીએ છીએ.',
                'icon' => 'graduation-cap',
                'accent_color' => '#D97706',
                'sort_order' => 20,
                'type' => 'cms',
            ],
            [
                'slug' => 'jobs',
                'title_en' => 'Job Opportunities',
                'title_gu' => 'રોજગાર તકો',
                'description_en' => 'Verified local and government vacancies. Read the last date, the papers asked for, and send a question to the desk if the family needs a travel note or a form filled in Gujarati.',
                'description_gu' => 'સરકારી અને સ્થાનિક ભરતી. છેલ્લી તારીખ અને કાગળ વાંચો. પરિવારને મુસાફરી નોંધ કે ગુજરાતી ફોર્મ જોઈએ તો ડેસ્કને પૂછો.',
                'icon' => 'briefcase',
                'accent_color' => '#059669',
                'sort_order' => 30,
                'type' => 'cms',
            ],
            [
                'slug' => 'education',
                'title_en' => 'Education',
                'title_gu' => 'શિક્ષણ સહાય',
                'description_en' => 'Libraries, coaching and study guidance for school and first-generation college students. Mentors can be asked a question from the same case, so the answer stays on the timeline.',
                'description_gu' => 'શાળા અને પહેલી પેઢીના કોલેજ વિદ્યાર્થીઓ માટે પુસ્તકાલય, કોચિંગ અને અભ્યાસ માર્ગદર્શન. માર્ગદર્શકને એ જ કેસમાંથી પ્રશ્ન પૂછી શકાય, જેથી જવાબ ટાઈમલાઈન પર રહે.',
                'icon' => 'book-open',
                'accent_color' => '#2563EB',
                'sort_order' => 40,
                'type' => 'cms',
            ],
            [
                'slug' => 'health',
                'title_en' => 'Health Camps',
                'title_gu' => 'આરોગ્ય શિબિર',
                'description_en' => 'Mobile camps, sickle-cell screening and the nearest clinic. If a camp date is announced we put it here; if a family needs a referral, apply so a volunteer can walk with them.',
                'description_gu' => 'મોબાઇલ કેમ્પ, સિકલ-સેલ તપાસ અને નજીકનું દવાખાનું. કેમ્પની તારીખ અહીં મૂકાય છે; રેફરલ જોઈએ તો અરજી કરો જેથી સ્વયંસેવક સાથે ચાલે.',
                'icon' => 'activity',
                'accent_color' => '#E11D48',
                'sort_order' => 50,
                'type' => 'cms',
            ],
            [
                'slug' => 'blood',
                'title_en' => 'Emergency Blood',
                'title_gu' => 'ઇમરજન્સી રક્ત સહાય',
                'description_en' => 'Urgent donor matching across talukas. Write the blood group, units and a phone that stays on. The desk alerts volunteers; this is not a blood bank, it is a neighbour-to-neighbour call.',
                'description_gu' => 'તાલુકા પ્રમાણે તાત્કાલિક રક્તદાતા. ગ્રુપ, યુનિટ અને ચાલુ ફોન લખો. ડેસ્ક સ્વયંસેવકને જાણ કરે છે; આ બ્લડ બેંક નહીં, પડોશીની હાકલ છે.',
                'icon' => 'droplet',
                'accent_color' => '#DC2626',
                'sort_order' => 60,
                'type' => 'operational',
                'show_apply_form' => false,
            ],
            [
                'slug' => 'mock_tests',
                'title_en' => 'Exam Preparation',
                'title_gu' => 'પરીક્ષા તૈયારી',
                'description_en' => 'Mock tests for GPSC, Police and Forest Guard. Sit the paper at a village centre, see which topics are weak, and ask a mentor to plan the next week of study.',
                'description_gu' => 'GPSC, પોલીસ અને ફોરેસ્ટ ગાર્ડ માટે મોક ટેસ્ટ. ગામના કેન્દ્રમાં પેપર લખો, નબળા મુદ્દા જુઓ, અને આગલના અઠવાડિયા માટે માર્ગદર્શકને પૂછો.',
                'icon' => 'clipboard-list',
                'accent_color' => '#0F766E',
                'sort_order' => 70,
                'type' => 'cms',
            ],
            [
                'slug' => 'sakhi',
                'title_en' => 'Sakhi Circles',
                'title_gu' => 'સખી મંડળ',
                'description_en' => 'Self-help groups and micro-enterprise. Circles meet for savings, a small shop idea, or a joint application. The agevan can send a request when the group needs a form or a market link.',
                'description_gu' => 'સવાધ જૂથ અને ગૃહઉદ્યોગ. મંડળ બચત, નાનું દુકાનનો વિચાર અથવા સાઝી અરજી માટે મળે છે. આગેવાન જૂથને ફોર્મ કે બજાર જોડાણું જોઈએ ત્યારે વિનંતી મોકલી શકે.',
                'icon' => 'heart-handshake',
                'accent_color' => '#DB2777',
                'sort_order' => 80,
                'type' => 'cms',
            ],
            [
                'slug' => 'village_reports',
                'title_en' => 'Village Reports',
                'title_gu' => 'ગામ પ્રશ્નો',
                'description_en' => 'Citizen notes on water, roads and power. Write what broke, since when, and who already went to the panchayat. A staff member can then carry the same note to the taluka desk.',
                'description_gu' => 'પાણી, રસ્તા અને વીજળીની નાગરિક નોંધ. શું તૂટ્યું, કયાથી અને પંચાયતમાં કોણ ગયું તે લખો. કર્મચારી એજ નોંધ તાલુકા ડેસ્ક સુધી લઈ શકે.',
                'icon' => 'map-pin',
                'accent_color' => '#B45309',
                'sort_order' => 90,
                'type' => 'operational',
            ],
            [
                'slug' => 'mentorship',
                'title_en' => 'Legal and Mentors',
                'title_gu' => 'કાનૂની માર્ગદર્શન',
                'description_en' => 'Legal aid and education mentors. Ask one clear question. The reply stays on the case so the family does not have to retell the story at every visit.',
                'description_gu' => 'કાનૂની સહાય અને શિક્ષણ માર્ગદર્શકો. એક સ્પષ્ટ પ્રશ્ન પૂછો. જવાબ કેસ પર રહે જેથી પરિવારને દરેક મુલાકાતે વાત ફરી કહેવું ન પડે.',
                'icon' => 'users',
                'accent_color' => '#4F46E5',
                'sort_order' => 100,
                'type' => 'cms',
            ],
            [
                'slug' => 'entrepreneurship',
                'title_en' => 'Tribal Business',
                'title_gu' => 'આદિવાસી ઉદ્યોગ',
                'description_en' => 'Livelihood ideas, forest produce and small tribal enterprise. We help write a simple plan and point to a grant or a training date, not a loan shop.',
                'description_gu' => 'આજીવિકાના વિચારો, જંગલ ઉત્પાદન અને નાનું આદિવાસી ઉદ્યોગ. સરળ યોજના લખવામાં મદદ કરીએ અને ગ્રાન્ટ કે તાલીમની તારીખ બતાવીએ, લોન દુકાન નહીં.',
                'icon' => 'sprout',
                'accent_color' => '#15803D',
                'sort_order' => 110,
                'type' => 'cms',
            ],
            [
                'slug' => 'volunteer',
                'title_en' => 'Volunteer Network',
                'title_gu' => 'સ્વયંસેવક જાળ',
                'description_en' => 'Join field work, translation and camp days. Tell us the days you can walk a village. Coordinators call when a family needs a neighbour, not a stranger from town.',
                'description_gu' => 'ક્ષેત્રીય કાર્ય, અનુવાદ અને કેમ્પના દિવસ સાથે જોડાઓ. કયા દિવસે ગામ ચાલી શકો તે લખો. પરિવારને પડોશી જોઈએ ત્યારે સંયોજક હાક મારશે.',
                'icon' => 'hand-heart',
                'accent_color' => '#0EA5E9',
                'sort_order' => 120,
                'type' => 'operational',
                'show_apply_form' => false,
            ],
            [
                'slug' => 'home',
                'title_en' => 'Home page blocks',
                'title_gu' => 'મુખ્ય પૃષ્ઠ બ્લોક',
                'description_en' => 'Hero, how the desk works, papers, FAQ. Not a public sector tile.',
                'description_gu' => 'હીરો, ડેસ્ક કેવી રીતે કામ કરે, કાગળ, પ્રશ્નો. જાહેર સેવા ટાઇલ નથી.',
                'icon' => 'home',
                'accent_color' => '#2D5A3D',
                'sort_order' => 0,
                'type' => 'cms',
                'show_apply_form' => false,
                'is_public' => false,
            ],
        ];

        foreach ($modules as $module) {
            ContentModule::updateOrCreate(
                ['slug' => $module['slug']],
                [
                    'title_en' => $module['title_en'],
                    'title_gu' => $module['title_gu'],
                    'description_en' => $module['description_en'],
                    'description_gu' => $module['description_gu'],
                    'icon' => $module['icon'],
                    'accent_color' => $module['accent_color'],
                    'is_enabled' => true,
                    'is_public' => $module['is_public'] ?? true,
                    'sort_order' => $module['sort_order'],
                    'show_apply_form' => $module['show_apply_form'] ?? true,
                    'type' => $module['type'],
                ]
            );
        }
    }

    private function migrateTypedRecords(): void
    {
        foreach (Scheme::query()->get() as $scheme) {
            $benefits = $scheme->benefits ?? [];
            $title = $benefits['title'] ?? Str::headline($scheme->slug);
            $benefit = $benefits['benefit'] ?? '';
            $this->upsertItem('schemes', $scheme->slug, [
                'title_en' => $title,
                'title_gu' => $title,
                'excerpt_en' => $benefit,
                'excerpt_gu' => $benefit,
                'body_en' => $benefit !== '' ? '<p>'.e($benefit).'</p>' : '',
                'body_gu' => $benefit !== '' ? '<p>'.e($benefit).'</p>' : '',
                'meta' => [
                    'required_documents' => $scheme->required_documents,
                    'process_steps' => $scheme->process_steps,
                    'eligibility_rules' => $scheme->eligibility_rules,
                ],
                'is_published' => (bool) $scheme->is_published,
            ]);
        }

        foreach (Scholarship::query()->get() as $row) {
            $rules = $row->eligibility_rules ?? [];
            $title = $rules['title'] ?? Str::headline($row->slug);
            $this->upsertItem('scholarships', $row->slug, [
                'title_en' => $title,
                'title_gu' => $title,
                'excerpt_en' => $row->amount ? 'Amount: Rs '.$row->amount : '',
                'excerpt_gu' => $row->amount ? 'Rakal: Rs '.$row->amount : '',
                'body_en' => $row->amount ? '<p>Scholarship amount Rs '.e((string) $row->amount).'</p>' : '',
                'body_gu' => $row->amount ? '<p>Chhatravruti rakam Rs '.e((string) $row->amount).'</p>' : '',
                'meta' => [
                    'amount' => $row->amount,
                    'deadline_at' => optional($row->deadline_at)->toDateString(),
                    'eligibility_rules' => $rules,
                ],
                'is_published' => (bool) $row->is_published,
            ]);
        }

        foreach (JobPosting::query()->get() as $row) {
            $this->upsertItem('jobs', Str::slug($row->title.'-'.$row->id), [
                'title_en' => $row->title,
                'title_gu' => $row->title,
                'excerpt_en' => trim($row->company.' · '.$row->location),
                'excerpt_gu' => trim($row->company.' · '.$row->location),
                'body_en' => '<p>'.e($row->company).' — '.e($row->location).'</p>',
                'body_gu' => '<p>'.e($row->company).' — '.e($row->location).'</p>',
                'meta' => [
                    'company' => $row->company,
                    'location' => $row->location,
                    'salary_range' => $row->salary_range,
                    'requirements' => $row->requirements,
                    'deadline_at' => optional($row->deadline_at)->toDateString(),
                ],
                'is_published' => (bool) $row->is_active,
            ]);
        }

        foreach (HealthCamp::query()->get() as $row) {
            $this->upsertItem('health', Str::slug($row->title.'-'.$row->id), [
                'title_en' => $row->title,
                'title_gu' => $row->title,
                'excerpt_en' => $row->address,
                'excerpt_gu' => $row->address,
                'body_en' => '<p>'.e($row->organizer ?? '').' — '.e($row->address).'</p>',
                'body_gu' => '<p>'.e($row->organizer ?? '').' — '.e($row->address).'</p>',
                'meta' => [
                    'organizer' => $row->organizer,
                    'address' => $row->address,
                    'district_id' => $row->district_id,
                    'scheduled_at' => optional($row->scheduled_at)->toIso8601String(),
                ],
                'is_published' => (bool) $row->is_active,
            ]);
        }

        foreach (BloodRequest::query()->get() as $row) {
            $this->upsertItem('blood', 'blood-'.$row->id, [
                'title_en' => ($row->patient_name ?? 'Patient').' needs '.$row->blood_group,
                'title_gu' => ($row->patient_name ?? 'Dardi').' ne '.$row->blood_group.' joie',
                'excerpt_en' => ($row->units_required ?? 1).' unit(s) required',
                'excerpt_gu' => ($row->units_required ?? 1).' unit jaruri',
                'body_en' => '<p>Contact: '.e($row->contact_phone ?? '').'</p>',
                'body_gu' => '<p>Sampark: '.e($row->contact_phone ?? '').'</p>',
                'meta' => [
                    'patient_name' => $row->patient_name,
                    'blood_group' => $row->blood_group,
                    'units_required' => $row->units_required,
                    'contact_phone' => $row->contact_phone,
                    'hospital_id' => $row->hospital_id,
                    'urgency' => $row->urgency ?? 'urgent',
                ],
                'is_published' => in_array($row->status, ['active', 'urgent', 'pending', 'open'], true),
            ]);
        }

        foreach (MockTest::query()->get() as $row) {
            $this->upsertItem('mock_tests', Str::slug($row->title.'-'.$row->id), [
                'title_en' => $row->title,
                'title_gu' => $row->title,
                'excerpt_en' => ($row->duration_minutes ?? 60).' minutes · '.$row->total_marks.' marks',
                'excerpt_gu' => ($row->duration_minutes ?? 60).' minute · '.$row->total_marks.' gun',
                'body_en' => '<p>Category: '.e($row->category).'</p>',
                'body_gu' => '<p>Shreni: '.e($row->category).'</p>',
                'meta' => [
                    'category' => $row->category,
                    'duration_minutes' => $row->duration_minutes,
                    'total_marks' => $row->total_marks,
                ],
                'is_published' => (bool) ($row->is_active ?? true),
            ]);
        }

        foreach (SakhiCircle::query()->get() as $row) {
            $this->upsertItem('sakhi', Str::slug($row->name.'-'.$row->id), [
                'title_en' => $row->name,
                'title_gu' => $row->name,
                'excerpt_en' => 'Leader: '.$row->leader_name,
                'excerpt_gu' => 'Agevan: '.$row->leader_name,
                'body_en' => '<p>'.e((string) ($row->members_count ?? 0)).' members. Phone: '.e($row->leader_phone).'</p>',
                'body_gu' => '<p>'.e((string) ($row->members_count ?? 0)).' sabhyo. Phone: '.e($row->leader_phone).'</p>',
                'meta' => [
                    'leader_name' => $row->leader_name,
                    'leader_phone' => $row->leader_phone,
                    'members_count' => $row->members_count,
                ],
                'is_published' => (bool) $row->is_active,
            ]);
        }

        foreach (VillageReport::query()->get() as $row) {
            $this->upsertItem('village_reports', Str::slug($row->title.'-'.$row->id), [
                'title_en' => $row->title,
                'title_gu' => $row->title,
                'excerpt_en' => $row->category,
                'excerpt_gu' => $row->category,
                'body_en' => '<p>'.e($row->description).'</p>',
                'body_gu' => '<p>'.e($row->description).'</p>',
                'meta' => [
                    'category' => $row->category,
                    'village_id' => $row->village_id,
                    'status' => $row->status,
                ],
                'is_published' => true,
            ]);
        }

        foreach (MentorProfile::query()->get() as $row) {
            $name = $row->name ?? $row->full_name ?? 'Mentor '.$row->id;
            $expertise = $row->expertise ?? $row->specialization ?? null;
            $expertiseText = is_array($expertise) ? implode(', ', $expertise) : (string) ($expertise ?? '');
            $this->upsertItem('mentorship', Str::slug($name.'-'.$row->id), [
                'title_en' => $name,
                'title_gu' => $name,
                'excerpt_en' => $expertiseText,
                'excerpt_gu' => $expertiseText,
                'body_en' => '<p>'.e($row->bio ?? $row->about ?? '').'</p>',
                'body_gu' => '<p>'.e($row->bio ?? $row->about ?? '').'</p>',
                'meta' => [
                    'phone' => $row->phone ?? null,
                    'expertise' => $expertise,
                ],
                'is_published' => (bool) ($row->is_active ?? true),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function upsertItem(string $moduleSlug, string $slug, array $data): void
    {
        $module = ContentModule::query()->where('slug', $moduleSlug)->first();
        if (! $module) {
            return;
        }

        ContentItem::updateOrCreate(
            ['module_id' => $module->id, 'slug' => $slug],
            $data
        );
    }

    private function seedGuidanceItems(): void
    {
        $items = [
            [
                'module' => 'schemes',
                'slug' => 'how-the-desk-helps',
                'title_en' => "\u{0048}\u{006F}\u{0077}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0073}\u{0063}\u{0068}\u{0065}\u{006D}\u{0065}\u{0020}\u{0064}\u{0065}\u{0073}\u{006B}\u{0020}\u{0073}\u{0069}\u{0074}\u{0073}\u{0020}\u{0077}\u{0069}\u{0074}\u{0068}\u{0020}\u{0061}\u{0020}\u{0066}\u{0061}\u{006D}\u{0069}\u{006C}\u{0079}",
                'title_gu' => "\u{0AAF}\u{0ACB}\u{0A9C}\u{0AA8}\u{0ABE}\u{0020}\u{0AA1}\u{0AC7}\u{0AB8}\u{0ACD}\u{0A95}\u{0020}\u{0AAA}\u{0AB0}\u{0ABF}\u{0AB5}\u{0ABE}\u{0AB0}\u{0020}\u{0AB8}\u{0ABE}\u{0AA5}\u{0AC7}\u{0020}\u{0A95}\u{0AC7}\u{0AB5}\u{0AC0}\u{0020}\u{0AB0}\u{0AC0}\u{0AA4}\u{0AC7}\u{0020}\u{0AAC}\u{0AC7}\u{0AB8}\u{0AC7}\u{0020}\u{0A9B}\u{0AC7}",
                'excerpt_en' => "\u{0041}\u{0077}\u{0061}\u{0073}\u{002C}\u{0020}\u{0041}\u{0079}\u{0075}\u{0073}\u{0068}\u{006D}\u{0061}\u{006E}\u{002C}\u{0020}\u{004B}\u{0069}\u{0073}\u{0061}\u{006E}\u{0020}\u{0053}\u{0061}\u{0068}\u{0061}\u{0079}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0046}\u{006F}\u{0072}\u{0065}\u{0073}\u{0074}\u{0020}\u{0052}\u{0069}\u{0067}\u{0068}\u{0074}\u{0073}\u{0020}\u{0061}\u{0072}\u{0065}\u{0020}\u{006E}\u{006F}\u{0074}\u{0020}\u{0061}\u{0020}\u{006C}\u{0069}\u{0073}\u{0074}\u{0020}\u{0074}\u{006F}\u{0020}\u{0074}\u{0069}\u{0063}\u{006B}\u{002E}\u{0020}\u{0054}\u{0068}\u{0065}\u{0020}\u{0063}\u{006F}\u{006F}\u{0072}\u{0064}\u{0069}\u{006E}\u{0061}\u{0074}\u{006F}\u{0072}\u{0020}\u{0066}\u{0069}\u{0072}\u{0073}\u{0074}\u{0020}\u{0061}\u{0073}\u{006B}\u{0073}\u{0020}\u{0077}\u{0068}\u{0069}\u{0063}\u{0068}\u{0020}\u{0070}\u{0061}\u{0070}\u{0065}\u{0072}\u{0073}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0068}\u{006F}\u{0075}\u{0073}\u{0065}\u{0068}\u{006F}\u{006C}\u{0064}\u{0020}\u{0061}\u{006C}\u{0072}\u{0065}\u{0061}\u{0064}\u{0079}\u{0020}\u{0068}\u{0061}\u{0073}\u{002C}\u{0020}\u{0077}\u{0068}\u{0069}\u{0063}\u{0068}\u{0020}\u{006F}\u{0066}\u{0066}\u{0069}\u{0063}\u{0065}\u{0072}\u{0020}\u{006C}\u{0061}\u{0073}\u{0074}\u{0020}\u{0076}\u{0069}\u{0073}\u{0069}\u{0074}\u{0065}\u{0064}\u{002C}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0077}\u{0068}\u{0065}\u{0074}\u{0068}\u{0065}\u{0072}\u{0020}\u{0061}\u{006E}\u{0079}\u{006F}\u{006E}\u{0065}\u{0020}\u{0069}\u{006E}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0066}\u{0061}\u{006D}\u{0069}\u{006C}\u{0079}\u{0020}\u{0063}\u{0061}\u{006E}\u{0020}\u{0074}\u{0072}\u{0061}\u{0076}\u{0065}\u{006C}\u{0020}\u{0074}\u{006F}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0074}\u{0061}\u{006C}\u{0075}\u{006B}\u{0061}\u{0020}\u{006F}\u{006E}\u{0020}\u{0061}\u{0020}\u{0077}\u{006F}\u{0072}\u{006B}\u{0069}\u{006E}\u{0067}\u{0020}\u{0064}\u{0061}\u{0079}\u{002E}",
                'excerpt_gu' => "\u{0A86}\u{0AB5}\u{0ABE}\u{0AB8}\u{002C}\u{0020}\u{0A86}\u{0AAF}\u{0AC1}\u{0AB7}\u{0ACD}\u{0AAE}\u{0ABE}\u{0AA8}\u{002C}\u{0020}\u{0A95}\u{0ABF}\u{0AB8}\u{0ABE}\u{0AA8}\u{0020}\u{0AB8}\u{0AB9}\u{0ABE}\u{0AAF}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0A9C}\u{0A82}\u{0A97}\u{0AB2}\u{0020}\u{0AB9}\u{0A95}\u{0020}\u{0A9F}\u{0ABF}\u{0A95}\u{0020}\u{0A95}\u{0AB0}\u{0AB5}\u{0ABE}\u{0AA8}\u{0AC0}\u{0020}\u{0AAF}\u{0ABE}\u{0AA6}\u{0AC0}\u{0020}\u{0AA8}\u{0AA5}\u{0AC0}\u{002E}\u{0020}\u{0AB8}\u{0A82}\u{0AAF}\u{0ACB}\u{0A9C}\u{0A95}\u{0020}\u{0AAA}\u{0AB9}\u{0AC7}\u{0AB2}\u{0ABE}\u{0020}\u{0AAA}\u{0AC2}\u{0A9B}\u{0AC7}\u{0020}\u{0A9B}\u{0AC7}\u{0020}\u{0A95}\u{0AC7}\u{0020}\u{0A98}\u{0AB0}\u{0AAE}\u{0ABE}\u{0A82}\u{0020}\u{0A95}\u{0AAF}\u{0ABE}\u{0020}\u{0A95}\u{0ABE}\u{0A97}\u{0AB3}\u{0020}\u{0A9B}\u{0AC7}\u{002C}\u{0020}\u{0A9B}\u{0AC7}\u{0AB2}\u{0ACD}\u{0AB2}\u{0AC7}\u{0020}\u{0A95}\u{0AAF}\u{0ACB}\u{0020}\u{0A85}\u{0AA7}\u{0ABF}\u{0A95}\u{0ABE}\u{0AB0}\u{0AC0}\u{0020}\u{0A86}\u{0AB5}\u{0ACD}\u{0AAF}\u{0ACB}\u{002C}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0A95}\u{0ABE}\u{0AAE}\u{0AA8}\u{0ABE}\u{0020}\u{0AA6}\u{0ABF}\u{0AB5}\u{0AB8}\u{0AC7}\u{0020}\u{0AA4}\u{0ABE}\u{0AB2}\u{0AC1}\u{0A95}\u{0ABE}\u{0020}\u{0A9C}\u{0A88}\u{0020}\u{0AB6}\u{0A95}\u{0AC7}\u{0020}\u{0AA4}\u{0AC7}\u{0AB5}\u{0AC1}\u{0A82}\u{0020}\u{0A95}\u{0ACB}\u{0AA3}\u{0020}\u{0A9B}\u{0AC7}\u{002E}",
                'body_en' => "\u{003C}\u{0070}\u{003E}\u{0042}\u{0072}\u{0069}\u{006E}\u{0067}\u{0020}\u{0077}\u{0068}\u{0061}\u{0074}\u{0065}\u{0076}\u{0065}\u{0072}\u{0020}\u{0079}\u{006F}\u{0075}\u{0020}\u{0061}\u{006C}\u{0072}\u{0065}\u{0061}\u{0064}\u{0079}\u{0020}\u{0068}\u{0061}\u{0076}\u{0065}\u{0020}\u{2014}\u{0020}\u{0072}\u{0061}\u{0074}\u{0069}\u{006F}\u{006E}\u{0020}\u{0063}\u{0061}\u{0072}\u{0064}\u{002C}\u{0020}\u{0041}\u{0061}\u{0064}\u{0068}\u{0061}\u{0061}\u{0072}\u{002C}\u{0020}\u{0063}\u{0061}\u{0073}\u{0074}\u{0065}\u{0020}\u{0063}\u{0065}\u{0072}\u{0074}\u{0069}\u{0066}\u{0069}\u{0063}\u{0061}\u{0074}\u{0065}\u{002C}\u{0020}\u{006F}\u{0072}\u{0020}\u{0061}\u{0020}\u{0070}\u{0072}\u{0065}\u{0076}\u{0069}\u{006F}\u{0075}\u{0073}\u{0020}\u{0072}\u{0065}\u{006A}\u{0065}\u{0063}\u{0074}\u{0069}\u{006F}\u{006E}\u{0020}\u{0073}\u{006C}\u{0069}\u{0070}\u{002E}\u{0020}\u{0057}\u{0065}\u{0020}\u{0070}\u{0068}\u{006F}\u{0074}\u{006F}\u{0063}\u{006F}\u{0070}\u{0079}\u{0020}\u{0061}\u{0074}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0076}\u{0069}\u{006C}\u{006C}\u{0061}\u{0067}\u{0065}\u{0020}\u{0064}\u{0065}\u{0073}\u{006B}\u{0020}\u{0073}\u{006F}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{006F}\u{0072}\u{0069}\u{0067}\u{0069}\u{006E}\u{0061}\u{006C}\u{0020}\u{0073}\u{0074}\u{0061}\u{0079}\u{0073}\u{0020}\u{0077}\u{0069}\u{0074}\u{0068}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0066}\u{0061}\u{006D}\u{0069}\u{006C}\u{0079}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}\u{003C}\u{0070}\u{003E}\u{0049}\u{0066}\u{0020}\u{0061}\u{0020}\u{0066}\u{006F}\u{0072}\u{006D}\u{0020}\u{006D}\u{0075}\u{0073}\u{0074}\u{0020}\u{0062}\u{0065}\u{0020}\u{0066}\u{0069}\u{006C}\u{006C}\u{0065}\u{0064}\u{0020}\u{0069}\u{006E}\u{0020}\u{0047}\u{0075}\u{006A}\u{0061}\u{0072}\u{0061}\u{0074}\u{0069}\u{002C}\u{0020}\u{0061}\u{0020}\u{0076}\u{006F}\u{006C}\u{0075}\u{006E}\u{0074}\u{0065}\u{0065}\u{0072}\u{0020}\u{0077}\u{0072}\u{0069}\u{0074}\u{0065}\u{0073}\u{0020}\u{0069}\u{0074}\u{0020}\u{0066}\u{0072}\u{006F}\u{006D}\u{0020}\u{0079}\u{006F}\u{0075}\u{0072}\u{0020}\u{0073}\u{0070}\u{006F}\u{006B}\u{0065}\u{006E}\u{0020}\u{0061}\u{006E}\u{0073}\u{0077}\u{0065}\u{0072}\u{0073}\u{002E}\u{0020}\u{0054}\u{0068}\u{0065}\u{0020}\u{0063}\u{0061}\u{0073}\u{0065}\u{0020}\u{006E}\u{0075}\u{006D}\u{0062}\u{0065}\u{0072}\u{0020}\u{006F}\u{006E}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0065}\u{006D}\u{0061}\u{0069}\u{006C}\u{0020}\u{0069}\u{0073}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0073}\u{0061}\u{006D}\u{0065}\u{0020}\u{0066}\u{0069}\u{006C}\u{0065}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0073}\u{0074}\u{0061}\u{0066}\u{0066}\u{0020}\u{0077}\u{0069}\u{006C}\u{006C}\u{0020}\u{006F}\u{0070}\u{0065}\u{006E}\u{0020}\u{006E}\u{0065}\u{0078}\u{0074}\u{0020}\u{0077}\u{0065}\u{0065}\u{006B}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}\u{003C}\u{0070}\u{003E}\u{0057}\u{0065}\u{0020}\u{0064}\u{006F}\u{0020}\u{006E}\u{006F}\u{0074}\u{0020}\u{0063}\u{006F}\u{006C}\u{006C}\u{0065}\u{0063}\u{0074}\u{0020}\u{0065}\u{0078}\u{0074}\u{0072}\u{0061}\u{0020}\u{006D}\u{006F}\u{006E}\u{0065}\u{0079}\u{0020}\u{0066}\u{006F}\u{0072}\u{0020}\u{0061}\u{0020}\u{0067}\u{006F}\u{0076}\u{0065}\u{0072}\u{006E}\u{006D}\u{0065}\u{006E}\u{0074}\u{0020}\u{0073}\u{0063}\u{0068}\u{0065}\u{006D}\u{0065}\u{002E}\u{0020}\u{0049}\u{0066}\u{0020}\u{0073}\u{006F}\u{006D}\u{0065}\u{006F}\u{006E}\u{0065}\u{0020}\u{0061}\u{0074}\u{0020}\u{0061}\u{0020}\u{0077}\u{0069}\u{006E}\u{0064}\u{006F}\u{0077}\u{0020}\u{0061}\u{0073}\u{006B}\u{0073}\u{0020}\u{0066}\u{006F}\u{0072}\u{0020}\u{0061}\u{0020}\u{0066}\u{0065}\u{0065}\u{0020}\u{0074}\u{0068}\u{0061}\u{0074}\u{0020}\u{0069}\u{0073}\u{0020}\u{006E}\u{006F}\u{0074}\u{0020}\u{0070}\u{0072}\u{0069}\u{006E}\u{0074}\u{0065}\u{0064}\u{0020}\u{006F}\u{006E}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0066}\u{006F}\u{0072}\u{006D}\u{002C}\u{0020}\u{0074}\u{0065}\u{006C}\u{006C}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0063}\u{006F}\u{006F}\u{0072}\u{0064}\u{0069}\u{006E}\u{0061}\u{0074}\u{006F}\u{0072}\u{0020}\u{0073}\u{006F}\u{0020}\u{0069}\u{0074}\u{0020}\u{0069}\u{0073}\u{0020}\u{0077}\u{0072}\u{0069}\u{0074}\u{0074}\u{0065}\u{006E}\u{0020}\u{006F}\u{006E}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0074}\u{0069}\u{006D}\u{0065}\u{006C}\u{0069}\u{006E}\u{0065}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}",
                'body_gu' => "\u{003C}\u{0070}\u{003E}\u{0A9C}\u{0AC7}\u{0020}\u{0A95}\u{0ABE}\u{0A97}\u{0AB3}\u{0020}\u{0AAA}\u{0ABE}\u{0AB8}\u{0AC7}\u{0020}\u{0AB9}\u{0ACB}\u{0AAF}\u{0020}\u{0AA4}\u{0AC7}\u{0020}\u{0AB2}\u{0ABE}\u{0AB5}\u{0ACB}\u{0020}\u{2014}\u{0020}\u{0AB0}\u{0AC7}\u{0AB6}\u{0AA8}\u{0020}\u{0A95}\u{0ABE}\u{0AB0}\u{0ACD}\u{0AA1}\u{002C}\u{0020}\u{0A86}\u{0AA7}\u{0ABE}\u{0AB0}\u{002C}\u{0020}\u{0A9C}\u{0ABE}\u{0AA4}\u{0ABF}\u{0020}\u{0AAA}\u{0ACD}\u{0AB0}\u{0AAE}\u{0ABE}\u{0AA3}\u{0AAA}\u{0AA4}\u{0ACD}\u{0AB0}\u{0020}\u{0A85}\u{0AA5}\u{0AB5}\u{0ABE}\u{0020}\u{0A85}\u{0A97}\u{0ABE}\u{0A89}\u{0AA8}\u{0ACB}\u{0020}\u{0AA8}\u{0A95}\u{0ABE}\u{0AB0}\u{002E}\u{0020}\u{0A97}\u{0ABE}\u{0AAE}\u{0020}\u{0AA1}\u{0AC7}\u{0AB8}\u{0ACD}\u{0A95}\u{0020}\u{0AAA}\u{0AB0}\u{0020}\u{0AA8}\u{0A95}\u{0AB2}\u{0020}\u{0A95}\u{0AB0}\u{0AC0}\u{0A8F}\u{0020}\u{0A9B}\u{0AC0}\u{0A8F}\u{0020}\u{0A9C}\u{0AC7}\u{0AA5}\u{0AC0}\u{0020}\u{0AAE}\u{0AC2}\u{0AB3}\u{0020}\u{0A95}\u{0ABE}\u{0A97}\u{0AB3}\u{0020}\u{0A98}\u{0AB0}\u{0AC7}\u{0020}\u{0AB0}\u{0AB9}\u{0AC7}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}\u{003C}\u{0070}\u{003E}\u{0AAB}\u{0ACB}\u{0AB0}\u{0ACD}\u{0AAE}\u{0020}\u{0A97}\u{0AC1}\u{0A9C}\u{0AB0}\u{0ABE}\u{0AA4}\u{0AC0}\u{0AAE}\u{0ABE}\u{0A82}\u{0020}\u{0AAD}\u{0AB0}\u{0AB5}\u{0AC1}\u{0A82}\u{0020}\u{0AB9}\u{0ACB}\u{0AAF}\u{0020}\u{0AA4}\u{0ACB}\u{0020}\u{0AB8}\u{0ACD}\u{0AB5}\u{0AAF}\u{0A82}\u{0AB8}\u{0AC7}\u{0AB5}\u{0A95}\u{0020}\u{0AA4}\u{0AAE}\u{0ABE}\u{0AB0}\u{0ABE}\u{0020}\u{0AAC}\u{0ACB}\u{0AB2}\u{0AC7}\u{0AB2}\u{0ABE}\u{0020}\u{0A9C}\u{0AB5}\u{0ABE}\u{0AAC}\u{0AA5}\u{0AC0}\u{0020}\u{0AB2}\u{0A96}\u{0AC7}\u{0020}\u{0A9B}\u{0AC7}\u{002E}\u{0020}\u{0A87}\u{0AAE}\u{0AC7}\u{0A87}\u{0AB2}\u{0020}\u{0AAA}\u{0AB0}\u{0AA8}\u{0ACB}\u{0020}\u{0A95}\u{0AC7}\u{0AB8}\u{0020}\u{0AA8}\u{0A82}\u{0AAC}\u{0AB0}\u{0020}\u{0A8F}\u{0020}\u{0A9C}\u{0020}\u{0AAB}\u{0ABE}\u{0A87}\u{0AB2}\u{0020}\u{0A9B}\u{0AC7}\u{0020}\u{0A9C}\u{0AC7}\u{0020}\u{0A86}\u{0A97}\u{0AB2}\u{0ABE}\u{0020}\u{0A85}\u{0AA0}\u{0AB5}\u{0ABE}\u{0AA1}\u{0ABF}\u{0AAF}\u{0AC7}\u{0020}\u{0AB8}\u{0ACD}\u{0A9F}\u{0ABE}\u{0AAB}\u{0020}\u{0A96}\u{0ACB}\u{0AB2}\u{0AB6}\u{0AC7}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}\u{003C}\u{0070}\u{003E}\u{0AB8}\u{0AB0}\u{0A95}\u{0ABE}\u{0AB0}\u{0AC0}\u{0020}\u{0AAF}\u{0ACB}\u{0A9C}\u{0AA8}\u{0ABE}\u{0020}\u{0AAE}\u{0ABE}\u{0A9F}\u{0AC7}\u{0020}\u{0AB5}\u{0AA7}\u{0ABE}\u{0AB0}\u{0ABE}\u{0AA8}\u{0ABE}\u{0020}\u{0AAA}\u{0AC8}\u{0AB8}\u{0ABE}\u{0020}\u{0AA8}\u{0AA5}\u{0AC0}\u{0020}\u{0AB2}\u{0AC7}\u{0AA4}\u{0ABE}\u{002E}\u{0020}\u{0AAB}\u{0ACB}\u{0AB0}\u{0ACD}\u{0AAE}\u{0020}\u{0AAA}\u{0AB0}\u{0020}\u{0AA8}\u{0020}\u{0AB2}\u{0A96}\u{0AC7}\u{0AB2}\u{0AC0}\u{0020}\u{0AAB}\u{0AC0}\u{0020}\u{0AAE}\u{0ABE}\u{0A97}\u{0AC7}\u{0020}\u{0AA4}\u{0ACB}\u{0020}\u{0AB8}\u{0A82}\u{0AAF}\u{0ACB}\u{0A9C}\u{0A95}\u{0AA8}\u{0AC7}\u{0020}\u{0A95}\u{0AB9}\u{0ACB}\u{0020}\u{0A9C}\u{0AC7}\u{0AA5}\u{0AC0}\u{0020}\u{0A9F}\u{0ABE}\u{0A88}\u{0AAE}\u{0AB2}\u{0ABE}\u{0A87}\u{0AA8}\u{0020}\u{0AAA}\u{0AB0}\u{0020}\u{0AA8}\u{0ACB}\u{0A82}\u{0AA7}\u{0ABE}\u{0AAF}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}",
                'is_published' => true,
            ],
            [
                'module' => 'scholarships',
                'slug' => 'papers-and-deadlines',
                'title_en' => "\u{0050}\u{0061}\u{0070}\u{0065}\u{0072}\u{0073}\u{002C}\u{0020}\u{006C}\u{0061}\u{0073}\u{0074}\u{0020}\u{0064}\u{0061}\u{0074}\u{0065}\u{0073}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0077}\u{0068}\u{006F}\u{0020}\u{0063}\u{0061}\u{006E}\u{0020}\u{0061}\u{0070}\u{0070}\u{006C}\u{0079}",
                'title_gu' => "\u{0A95}\u{0ABE}\u{0A97}\u{0AB3}\u{002C}\u{0020}\u{0A9B}\u{0AC7}\u{0AB2}\u{0ACD}\u{0AB2}\u{0AC0}\u{0020}\u{0AA4}\u{0ABE}\u{0AB0}\u{0AC0}\u{0A96}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0A95}\u{0ACB}\u{0AA3}\u{0020}\u{0A85}\u{0AB0}\u{0A9C}\u{0AC0}\u{0020}\u{0A95}\u{0AB0}\u{0AC0}\u{0020}\u{0AB6}\u{0A95}\u{0AC7}",
                'excerpt_en' => "\u{0050}\u{006F}\u{0073}\u{0074}\u{002D}\u{006D}\u{0061}\u{0074}\u{0072}\u{0069}\u{0063}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0049}\u{0054}\u{0049}\u{0020}\u{0067}\u{0072}\u{0061}\u{006E}\u{0074}\u{0073}\u{0020}\u{0063}\u{006C}\u{006F}\u{0073}\u{0065}\u{0020}\u{006F}\u{006E}\u{0020}\u{0061}\u{0020}\u{0070}\u{0072}\u{0069}\u{006E}\u{0074}\u{0065}\u{0064}\u{0020}\u{0064}\u{0061}\u{0074}\u{0065}\u{002E}\u{0020}\u{0052}\u{0065}\u{0061}\u{0064}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{006C}\u{0061}\u{0073}\u{0074}\u{0020}\u{0064}\u{0061}\u{0074}\u{0065}\u{0020}\u{0066}\u{0069}\u{0072}\u{0073}\u{0074}\u{002C}\u{0020}\u{0074}\u{0068}\u{0065}\u{006E}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0069}\u{006E}\u{0063}\u{006F}\u{006D}\u{0065}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0063}\u{0061}\u{0073}\u{0074}\u{0065}\u{0020}\u{0070}\u{0061}\u{0070}\u{0065}\u{0072}\u{0073}\u{002E}\u{0020}\u{0049}\u{0066}\u{0020}\u{0061}\u{0020}\u{0073}\u{0074}\u{0075}\u{0064}\u{0065}\u{006E}\u{0074}\u{0020}\u{0069}\u{0073}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0066}\u{0069}\u{0072}\u{0073}\u{0074}\u{0020}\u{0069}\u{006E}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0066}\u{0061}\u{006D}\u{0069}\u{006C}\u{0079}\u{0020}\u{0074}\u{006F}\u{0020}\u{0073}\u{0069}\u{0074}\u{0020}\u{0063}\u{006F}\u{006C}\u{006C}\u{0065}\u{0067}\u{0065}\u{002C}\u{0020}\u{0073}\u{0061}\u{0079}\u{0020}\u{0073}\u{006F}\u{0020}\u{2014}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{006D}\u{0065}\u{006E}\u{0074}\u{006F}\u{0072}\u{0020}\u{0077}\u{0069}\u{006C}\u{006C}\u{0020}\u{006B}\u{0065}\u{0065}\u{0070}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0073}\u{0061}\u{006D}\u{0065}\u{0020}\u{0063}\u{0061}\u{0073}\u{0065}\u{0020}\u{0075}\u{006E}\u{0074}\u{0069}\u{006C}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0072}\u{0065}\u{0063}\u{0065}\u{0069}\u{0070}\u{0074}\u{0020}\u{0069}\u{0073}\u{0020}\u{0075}\u{0070}\u{006C}\u{006F}\u{0061}\u{0064}\u{0065}\u{0064}\u{002E}",
                'excerpt_gu' => "\u{0AAA}\u{0ACB}\u{0AB8}\u{0ACD}\u{0A9F}\u{002D}\u{0AAE}\u{0AC7}\u{0A9F}\u{0ACD}\u{0AB0}\u{0ABF}\u{0A95}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0049}\u{0054}\u{0049}\u{0020}\u{0A97}\u{0ACD}\u{0AB0}\u{0ABE}\u{0AA8}\u{0ACD}\u{0A9F}\u{0020}\u{0A9B}\u{0ABE}\u{0AAA}\u{0AC7}\u{0AB2}\u{0AC0}\u{0020}\u{0AA4}\u{0ABE}\u{0AB0}\u{0AC0}\u{0A96}\u{0AC7}\u{0020}\u{0AAC}\u{0A82}\u{0AA7}\u{0020}\u{0AA5}\u{0ABE}\u{0AAF}\u{0020}\u{0A9B}\u{0AC7}\u{002E}\u{0020}\u{0AAA}\u{0AB9}\u{0AC7}\u{0AB2}\u{0ABE}\u{0020}\u{0A9B}\u{0AC7}\u{0AB2}\u{0ACD}\u{0AB2}\u{0AC0}\u{0020}\u{0AA4}\u{0ABE}\u{0AB0}\u{0AC0}\u{0A96}\u{0020}\u{0AB5}\u{0ABE}\u{0A82}\u{0A9A}\u{0ACB}\u{002C}\u{0020}\u{0AAA}\u{0A9B}\u{0AC0}\u{0020}\u{0A86}\u{0AB5}\u{0A95}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0A9C}\u{0ABE}\u{0AA4}\u{0ABF}\u{0AA8}\u{0ABE}\u{0020}\u{0A95}\u{0ABE}\u{0A97}\u{0AB3}\u{002E}\u{0020}\u{0AAA}\u{0AB0}\u{0ABF}\u{0AB5}\u{0ABE}\u{0AB0}\u{0AAE}\u{0ABE}\u{0A82}\u{0020}\u{0AAA}\u{0AB9}\u{0AC7}\u{0AB2}\u{0AC0}\u{0020}\u{0AB5}\u{0ABE}\u{0AB0}\u{0020}\u{0A95}\u{0ACB}\u{0AB2}\u{0AC7}\u{0A9C}\u{0020}\u{0AAC}\u{0AC7}\u{0AB8}\u{0AA4}\u{0ABE}\u{0020}\u{0AB9}\u{0ACB}\u{0AAF}\u{0020}\u{0AA4}\u{0ACB}\u{0020}\u{0A95}\u{0AB9}\u{0ACB}\u{0020}\u{2014}\u{0020}\u{0AB0}\u{0AB8}\u{0AC0}\u{0AA6}\u{0020}\u{0A85}\u{0AAA}\u{0AB2}\u{0ACB}\u{0AA1}\u{0020}\u{0AA5}\u{0ABE}\u{0AAF}\u{0020}\u{0AA4}\u{0ACD}\u{0AAF}\u{0ABE}\u{0A82}\u{0020}\u{0AB8}\u{0AC1}\u{0AA7}\u{0AC0}\u{0020}\u{0AAE}\u{0ABE}\u{0AB0}\u{0ACD}\u{0A97}\u{0AA6}\u{0AB0}\u{0ACD}\u{0AB6}\u{0A95}\u{0020}\u{0A8F}\u{0020}\u{0A9C}\u{0020}\u{0A95}\u{0AC7}\u{0AB8}\u{0020}\u{0AB0}\u{0ABE}\u{0A96}\u{0AB6}\u{0AC7}\u{002E}",
                'body_en' => "\u{003C}\u{0070}\u{003E}\u{004B}\u{0065}\u{0065}\u{0070}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{006D}\u{0061}\u{0072}\u{006B}\u{0073}\u{0068}\u{0065}\u{0065}\u{0074}\u{002C}\u{0020}\u{0063}\u{0061}\u{0073}\u{0074}\u{0065}\u{0020}\u{0063}\u{0065}\u{0072}\u{0074}\u{0069}\u{0066}\u{0069}\u{0063}\u{0061}\u{0074}\u{0065}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0061}\u{0020}\u{0062}\u{0061}\u{006E}\u{006B}\u{0020}\u{0070}\u{0061}\u{0073}\u{0073}\u{0062}\u{006F}\u{006F}\u{006B}\u{0020}\u{0070}\u{0061}\u{0067}\u{0065}\u{0020}\u{0072}\u{0065}\u{0061}\u{0064}\u{0079}\u{002E}\u{0020}\u{004D}\u{0061}\u{006E}\u{0079}\u{0020}\u{0064}\u{0065}\u{0073}\u{006B}\u{0073}\u{0020}\u{0061}\u{0073}\u{006B}\u{0020}\u{0066}\u{006F}\u{0072}\u{0020}\u{0061}\u{0020}\u{0063}\u{0061}\u{006E}\u{0063}\u{0065}\u{006C}\u{006C}\u{0065}\u{0064}\u{0020}\u{0063}\u{0068}\u{0065}\u{0071}\u{0075}\u{0065}\u{003B}\u{0020}\u{0069}\u{0066}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0076}\u{0069}\u{006C}\u{006C}\u{0061}\u{0067}\u{0065}\u{0020}\u{0068}\u{0061}\u{0073}\u{0020}\u{006E}\u{006F}\u{0020}\u{0062}\u{0061}\u{006E}\u{006B}\u{002C}\u{0020}\u{0077}\u{0072}\u{0069}\u{0074}\u{0065}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{006E}\u{0065}\u{0061}\u{0072}\u{0065}\u{0073}\u{0074}\u{0020}\u{0062}\u{0072}\u{0061}\u{006E}\u{0063}\u{0068}\u{0020}\u{006F}\u{006E}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0066}\u{006F}\u{0072}\u{006D}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0074}\u{0065}\u{006C}\u{006C}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0063}\u{006F}\u{006F}\u{0072}\u{0064}\u{0069}\u{006E}\u{0061}\u{0074}\u{006F}\u{0072}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}\u{003C}\u{0070}\u{003E}\u{0041}\u{0020}\u{006C}\u{0061}\u{0074}\u{0065}\u{0020}\u{0061}\u{0070}\u{0070}\u{006C}\u{0069}\u{0063}\u{0061}\u{0074}\u{0069}\u{006F}\u{006E}\u{0020}\u{0069}\u{0073}\u{0020}\u{0073}\u{0074}\u{0069}\u{006C}\u{006C}\u{0020}\u{0077}\u{006F}\u{0072}\u{0074}\u{0068}\u{0020}\u{0073}\u{0065}\u{006E}\u{0064}\u{0069}\u{006E}\u{0067}\u{0020}\u{0069}\u{0066}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0070}\u{006F}\u{0072}\u{0074}\u{0061}\u{006C}\u{0020}\u{0069}\u{0073}\u{0020}\u{006F}\u{0070}\u{0065}\u{006E}\u{002E}\u{0020}\u{0054}\u{0068}\u{0065}\u{0020}\u{0074}\u{0069}\u{006D}\u{0065}\u{006C}\u{0069}\u{006E}\u{0065}\u{0020}\u{0077}\u{0069}\u{006C}\u{006C}\u{0020}\u{0073}\u{0068}\u{006F}\u{0077}\u{0020}\u{0077}\u{0068}\u{0065}\u{0074}\u{0068}\u{0065}\u{0072}\u{0020}\u{0073}\u{0074}\u{0061}\u{0066}\u{0066}\u{0020}\u{0061}\u{0073}\u{006B}\u{0065}\u{0064}\u{0020}\u{0066}\u{006F}\u{0072}\u{0020}\u{006F}\u{006E}\u{0065}\u{0020}\u{006D}\u{006F}\u{0072}\u{0065}\u{0020}\u{0070}\u{0061}\u{0070}\u{0065}\u{0072}\u{0020}\u{006F}\u{0072}\u{0020}\u{0077}\u{0068}\u{0065}\u{0074}\u{0068}\u{0065}\u{0072}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0077}\u{0069}\u{006E}\u{0064}\u{006F}\u{0077}\u{0020}\u{0063}\u{006C}\u{006F}\u{0073}\u{0065}\u{0064}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}",
                'body_gu' => "\u{003C}\u{0070}\u{003E}\u{0AAE}\u{0ABE}\u{0AB0}\u{0ACD}\u{0A95}\u{0AB6}\u{0AC0}\u{0A9F}\u{002C}\u{0020}\u{0A9C}\u{0ABE}\u{0AA4}\u{0ABF}\u{0020}\u{0AAA}\u{0ACD}\u{0AB0}\u{0AAE}\u{0ABE}\u{0AA3}\u{0AAA}\u{0AA4}\u{0ACD}\u{0AB0}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0AAA}\u{0ABE}\u{0AB8}\u{0AAC}\u{0AC1}\u{0A95}\u{0AA8}\u{0AC1}\u{0A82}\u{0020}\u{0AAA}\u{0ABE}\u{0AA8}\u{0AC1}\u{0A82}\u{0020}\u{0AA4}\u{0AC8}\u{0AAF}\u{0ABE}\u{0AB0}\u{0020}\u{0AB0}\u{0ABE}\u{0A96}\u{0ACB}\u{002E}\u{0020}\u{0A98}\u{0AA3}\u{0AC0}\u{0020}\u{0A9C}\u{0A97}\u{0ACD}\u{0AAF}\u{0ABE}\u{0A8F}\u{0020}\u{0A95}\u{0AC7}\u{0AA8}\u{0ACD}\u{0AB8}\u{0AB2}\u{0020}\u{0A9A}\u{0AC7}\u{0A95}\u{0020}\u{0AAE}\u{0ABE}\u{0A97}\u{0AC7}\u{003B}\u{0020}\u{0A97}\u{0ABE}\u{0AAE}\u{0AAE}\u{0ABE}\u{0A82}\u{0020}\u{0AAC}\u{0AC7}\u{0A82}\u{0A95}\u{0020}\u{0AA8}\u{0020}\u{0AB9}\u{0ACB}\u{0AAF}\u{0020}\u{0AA4}\u{0ACB}\u{0020}\u{0AA8}\u{0A9C}\u{0AC0}\u{0A95}\u{0AA8}\u{0AC0}\u{0020}\u{0AB6}\u{0ABE}\u{0A96}\u{0ABE}\u{0020}\u{0AB2}\u{0A96}\u{0ACB}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0AB8}\u{0A82}\u{0AAF}\u{0ACB}\u{0A9C}\u{0A95}\u{0AA8}\u{0AC7}\u{0020}\u{0A95}\u{0AB9}\u{0ACB}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}\u{003C}\u{0070}\u{003E}\u{0AAA}\u{0ACB}\u{0AB0}\u{0ACD}\u{0A9F}\u{0AB2}\u{0020}\u{0A96}\u{0AC1}\u{0AB2}\u{0ACD}\u{0AB2}\u{0AC1}\u{0A82}\u{0020}\u{0AB9}\u{0ACB}\u{0AAF}\u{0020}\u{0AA4}\u{0ACB}\u{0020}\u{0AAE}\u{0ACB}\u{0AA1}\u{0AC0}\u{0020}\u{0A85}\u{0AB0}\u{0A9C}\u{0AC0}\u{0020}\u{0AAA}\u{0AA3}\u{0020}\u{0AAE}\u{0ACB}\u{0A95}\u{0AB2}\u{0AB5}\u{0ABE}\u{0020}\u{0A9C}\u{0AC7}\u{0AB5}\u{0AC0}\u{0020}\u{0A9B}\u{0AC7}\u{002E}\u{0020}\u{0A9F}\u{0ABE}\u{0A88}\u{0AAE}\u{0AB2}\u{0ABE}\u{0A87}\u{0AA8}\u{0020}\u{0AAC}\u{0AA4}\u{0ABE}\u{0AB5}\u{0AB6}\u{0AC7}\u{0020}\u{0A95}\u{0AC7}\u{0020}\u{0A8F}\u{0A95}\u{0020}\u{0A95}\u{0ABE}\u{0A97}\u{0AB3}\u{0020}\u{0AB5}\u{0AA7}\u{0AC1}\u{0020}\u{0AAE}\u{0ABE}\u{0A97}\u{0ACD}\u{0AAF}\u{0ACB}\u{0020}\u{0A95}\u{0AC7}\u{0020}\u{0AB5}\u{0ABF}\u{0A82}\u{0AA1}\u{0ACB}\u{0020}\u{0AAC}\u{0A82}\u{0AA7}\u{0020}\u{0AA5}\u{0A88}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}",
                'is_published' => true,
            ],
            [
                'module' => 'jobs',
                'slug' => 'reading-a-vacancy',
                'title_en' => "\u{0048}\u{006F}\u{0077}\u{0020}\u{0074}\u{006F}\u{0020}\u{0072}\u{0065}\u{0061}\u{0064}\u{0020}\u{0061}\u{0020}\u{0076}\u{0061}\u{0063}\u{0061}\u{006E}\u{0063}\u{0079}\u{0020}\u{0062}\u{0065}\u{0066}\u{006F}\u{0072}\u{0065}\u{0020}\u{0079}\u{006F}\u{0075}\u{0020}\u{0074}\u{0072}\u{0061}\u{0076}\u{0065}\u{006C}",
                'title_gu' => "\u{0AAE}\u{0AC1}\u{0AB8}\u{0ABE}\u{0AAB}\u{0AB0}\u{0AC0}\u{0020}\u{0AAA}\u{0AB9}\u{0AC7}\u{0AB2}\u{0ABE}\u{0A82}\u{0020}\u{0AAD}\u{0AB0}\u{0AA4}\u{0AC0}\u{0020}\u{0A95}\u{0AC7}\u{0AB5}\u{0AC0}\u{0020}\u{0AB0}\u{0AC0}\u{0AA4}\u{0AC7}\u{0020}\u{0AB5}\u{0ABE}\u{0A82}\u{0A9A}\u{0AB5}\u{0AC0}",
                'excerpt_en' => "\u{0041}\u{0020}\u{006C}\u{0069}\u{0073}\u{0074}\u{0069}\u{006E}\u{0067}\u{0020}\u{0068}\u{0065}\u{0072}\u{0065}\u{0020}\u{0069}\u{0073}\u{0020}\u{0061}\u{0020}\u{0076}\u{0065}\u{0072}\u{0069}\u{0066}\u{0069}\u{0065}\u{0064}\u{0020}\u{006E}\u{006F}\u{0074}\u{0069}\u{0063}\u{0065}\u{002C}\u{0020}\u{006E}\u{006F}\u{0074}\u{0020}\u{0061}\u{0020}\u{0070}\u{006C}\u{0061}\u{0063}\u{0065}\u{006D}\u{0065}\u{006E}\u{0074}\u{0020}\u{0067}\u{0075}\u{0061}\u{0072}\u{0061}\u{006E}\u{0074}\u{0065}\u{0065}\u{002E}\u{0020}\u{0043}\u{0068}\u{0065}\u{0063}\u{006B}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{006C}\u{0061}\u{0073}\u{0074}\u{0020}\u{0064}\u{0061}\u{0074}\u{0065}\u{002C}\u{0020}\u{0061}\u{0067}\u{0065}\u{0020}\u{006C}\u{0069}\u{006D}\u{0069}\u{0074}\u{002C}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0077}\u{0068}\u{0065}\u{0074}\u{0068}\u{0065}\u{0072}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0065}\u{0078}\u{0061}\u{006D}\u{0020}\u{0063}\u{0065}\u{006E}\u{0074}\u{0072}\u{0065}\u{0020}\u{0069}\u{0073}\u{0020}\u{0069}\u{006E}\u{0020}\u{0079}\u{006F}\u{0075}\u{0072}\u{0020}\u{0064}\u{0069}\u{0073}\u{0074}\u{0072}\u{0069}\u{0063}\u{0074}\u{002E}\u{0020}\u{0049}\u{0066}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0066}\u{0061}\u{006D}\u{0069}\u{006C}\u{0079}\u{0020}\u{006E}\u{0065}\u{0065}\u{0064}\u{0073}\u{0020}\u{0061}\u{0020}\u{0074}\u{0072}\u{0061}\u{0076}\u{0065}\u{006C}\u{0020}\u{006E}\u{006F}\u{0074}\u{0065}\u{0020}\u{006F}\u{0072}\u{0020}\u{0061}\u{0020}\u{0047}\u{0075}\u{006A}\u{0061}\u{0072}\u{0061}\u{0074}\u{0069}\u{0020}\u{0066}\u{006F}\u{0072}\u{006D}\u{002C}\u{0020}\u{0077}\u{0072}\u{0069}\u{0074}\u{0065}\u{0020}\u{0074}\u{0068}\u{0061}\u{0074}\u{0020}\u{006F}\u{006E}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0061}\u{0070}\u{0070}\u{006C}\u{0069}\u{0063}\u{0061}\u{0074}\u{0069}\u{006F}\u{006E}\u{002E}",
                'excerpt_gu' => "\u{0A85}\u{0AB9}\u{0AC0}\u{0A82}\u{0AA8}\u{0AC0}\u{0020}\u{0AAF}\u{0ABE}\u{0AA6}\u{0AC0}\u{0020}\u{0A9A}\u{0A95}\u{0ABE}\u{0AB8}\u{0ABE}\u{0AAF}\u{0AC7}\u{0AB2}\u{0AC0}\u{0020}\u{0AA8}\u{0ACB}\u{0A9F}\u{0ABF}\u{0AB8}\u{0020}\u{0A9B}\u{0AC7}\u{002C}\u{0020}\u{0AA8}\u{0ACB}\u{0A95}\u{0AB0}\u{0AC0}\u{0AA8}\u{0AC0}\u{0020}\u{0A96}\u{0ABE}\u{0AA4}\u{0AB0}\u{0AC0}\u{0020}\u{0AA8}\u{0AB9}\u{0AC0}\u{0A82}\u{002E}\u{0020}\u{0A9B}\u{0AC7}\u{0AB2}\u{0ACD}\u{0AB2}\u{0AC0}\u{0020}\u{0AA4}\u{0ABE}\u{0AB0}\u{0AC0}\u{0A96}\u{002C}\u{0020}\u{0A89}\u{0A82}\u{0AAE}\u{0AB0}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0AAA}\u{0AB0}\u{0AC0}\u{0A95}\u{0ACD}\u{0AB7}\u{0ABE}\u{0020}\u{0A95}\u{0AC7}\u{0AA8}\u{0ACD}\u{0AA6}\u{0ACD}\u{0AB0}\u{0020}\u{0AA4}\u{0AAE}\u{0ABE}\u{0AB0}\u{0ABE}\u{0020}\u{0A9C}\u{0ABF}\u{0AB2}\u{0ACD}\u{0AB2}\u{0ABE}\u{0AAE}\u{0ABE}\u{0A82}\u{0020}\u{0A9B}\u{0AC7}\u{0020}\u{0A95}\u{0AC7}\u{0020}\u{0AA8}\u{0AB9}\u{0AC0}\u{0A82}\u{0020}\u{0AA4}\u{0AC7}\u{0020}\u{0A9C}\u{0AC1}\u{0A93}\u{002E}\u{0020}\u{0AAE}\u{0AC1}\u{0AB8}\u{0ABE}\u{0AAB}\u{0AB0}\u{0AC0}\u{0020}\u{0AA8}\u{0ACB}\u{0A82}\u{0AA7}\u{0020}\u{0A95}\u{0AC7}\u{0020}\u{0A97}\u{0AC1}\u{0A9C}\u{0AB0}\u{0ABE}\u{0AA4}\u{0AC0}\u{0020}\u{0AAB}\u{0ACB}\u{0AB0}\u{0ACD}\u{0AAE}\u{0020}\u{0A9C}\u{0ACB}\u{0A88}\u{0A8F}\u{0020}\u{0AA4}\u{0ACB}\u{0020}\u{0A85}\u{0AB0}\u{0A9C}\u{0AC0}\u{0AAE}\u{0ABE}\u{0A82}\u{0020}\u{0AB2}\u{0A96}\u{0ACB}\u{002E}",
                'body_en' => "\u{003C}\u{0070}\u{003E}\u{0043}\u{0061}\u{0072}\u{0072}\u{0079}\u{0020}\u{0074}\u{0077}\u{006F}\u{0020}\u{0070}\u{0068}\u{006F}\u{0074}\u{006F}\u{0067}\u{0072}\u{0061}\u{0070}\u{0068}\u{0073}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0061}\u{0020}\u{0063}\u{006F}\u{0070}\u{0079}\u{0020}\u{006F}\u{0066}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0041}\u{0061}\u{0064}\u{0068}\u{0061}\u{0061}\u{0072}\u{002E}\u{0020}\u{0049}\u{0066}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{006E}\u{006F}\u{0074}\u{0069}\u{0063}\u{0065}\u{0020}\u{0073}\u{0061}\u{0079}\u{0073}\u{0020}\u{0077}\u{0061}\u{006C}\u{006B}\u{002D}\u{0069}\u{006E}\u{002C}\u{0020}\u{0072}\u{0065}\u{0061}\u{0063}\u{0068}\u{0020}\u{0062}\u{0065}\u{0066}\u{006F}\u{0072}\u{0065}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0063}\u{006F}\u{0075}\u{006E}\u{0074}\u{0065}\u{0072}\u{0020}\u{006F}\u{0070}\u{0065}\u{006E}\u{0073}\u{003B}\u{0020}\u{0069}\u{0066}\u{0020}\u{0069}\u{0074}\u{0020}\u{0069}\u{0073}\u{0020}\u{006F}\u{006E}\u{006C}\u{0069}\u{006E}\u{0065}\u{0020}\u{006F}\u{006E}\u{006C}\u{0079}\u{002C}\u{0020}\u{0073}\u{0069}\u{0074}\u{0020}\u{0077}\u{0069}\u{0074}\u{0068}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0064}\u{0065}\u{0073}\u{006B}\u{0020}\u{0073}\u{006F}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0075}\u{0070}\u{006C}\u{006F}\u{0061}\u{0064}\u{0020}\u{0069}\u{0073}\u{0020}\u{0064}\u{006F}\u{006E}\u{0065}\u{0020}\u{006F}\u{006E}\u{0020}\u{0061}\u{0020}\u{0077}\u{006F}\u{0072}\u{006B}\u{0069}\u{006E}\u{0067}\u{0020}\u{0063}\u{006F}\u{006E}\u{006E}\u{0065}\u{0063}\u{0074}\u{0069}\u{006F}\u{006E}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}\u{003C}\u{0070}\u{003E}\u{0041}\u{0073}\u{006B}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0063}\u{006F}\u{006F}\u{0072}\u{0064}\u{0069}\u{006E}\u{0061}\u{0074}\u{006F}\u{0072}\u{0020}\u{0069}\u{0066}\u{0020}\u{0061}\u{006E}\u{0079}\u{006F}\u{006E}\u{0065}\u{0020}\u{0066}\u{0072}\u{006F}\u{006D}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0073}\u{0061}\u{006D}\u{0065}\u{0020}\u{0076}\u{0069}\u{006C}\u{006C}\u{0061}\u{0067}\u{0065}\u{0020}\u{0061}\u{006C}\u{0072}\u{0065}\u{0061}\u{0064}\u{0079}\u{0020}\u{0061}\u{0070}\u{0070}\u{006C}\u{0069}\u{0065}\u{0064}\u{0020}\u{2014}\u{0020}\u{0073}\u{006F}\u{006D}\u{0065}\u{0074}\u{0069}\u{006D}\u{0065}\u{0073}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0073}\u{0061}\u{006D}\u{0065}\u{0020}\u{0064}\u{006F}\u{0063}\u{0075}\u{006D}\u{0065}\u{006E}\u{0074}\u{0020}\u{0073}\u{0065}\u{0074}\u{0020}\u{0063}\u{0061}\u{006E}\u{0020}\u{0062}\u{0065}\u{0020}\u{0072}\u{0065}\u{0075}\u{0073}\u{0065}\u{0064}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}",
                'body_gu' => "\u{003C}\u{0070}\u{003E}\u{0AAC}\u{0AC7}\u{0020}\u{0AAB}\u{0ACB}\u{0A9F}\u{0ACB}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0A86}\u{0AA7}\u{0ABE}\u{0AB0}\u{0AA8}\u{0AC0}\u{0020}\u{0AA8}\u{0A95}\u{0AB2}\u{0020}\u{0AB2}\u{0A88}\u{0020}\u{0A9C}\u{0ABE}\u{0A93}\u{002E}\u{0020}\u{0AB5}\u{0ACB}\u{0A95}\u{002D}\u{0A87}\u{0AA8}\u{0020}\u{0AB9}\u{0ACB}\u{0AAF}\u{0020}\u{0AA4}\u{0ACB}\u{0020}\u{0A95}\u{0ABE}\u{0A89}\u{0AA8}\u{0ACD}\u{0A9F}\u{0AB0}\u{0020}\u{0A96}\u{0AC2}\u{0AB2}\u{0AC7}\u{0020}\u{0AA4}\u{0AC7}\u{0020}\u{0AAA}\u{0AB9}\u{0AC7}\u{0AB2}\u{0ABE}\u{0A82}\u{0020}\u{0AAA}\u{0AB9}\u{0ACB}\u{0A82}\u{0A9A}\u{0ACB}\u{003B}\u{0020}\u{0AAB}\u{0A95}\u{0ACD}\u{0AA4}\u{0020}\u{0A93}\u{0AA8}\u{0AB2}\u{0ABE}\u{0A87}\u{0AA8}\u{0020}\u{0AB9}\u{0ACB}\u{0AAF}\u{0020}\u{0AA4}\u{0ACB}\u{0020}\u{0AA1}\u{0AC7}\u{0AB8}\u{0ACD}\u{0A95}\u{0020}\u{0AB8}\u{0ABE}\u{0AA5}\u{0AC7}\u{0020}\u{0AAC}\u{0AC7}\u{0AB8}\u{0AC0}\u{0AA8}\u{0AC7}\u{0020}\u{0A85}\u{0AAA}\u{0AB2}\u{0ACB}\u{0AA1}\u{0020}\u{0A95}\u{0AB0}\u{0ACB}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}\u{003C}\u{0070}\u{003E}\u{0A8F}\u{0020}\u{0A9C}\u{0020}\u{0A97}\u{0ABE}\u{0AAE}\u{0AAE}\u{0ABE}\u{0A82}\u{0AA5}\u{0AC0}\u{0020}\u{0A95}\u{0ACB}\u{0A87}\u{0A8F}\u{0020}\u{0A85}\u{0AB0}\u{0A9C}\u{0AC0}\u{0020}\u{0A95}\u{0AB0}\u{0AC0}\u{0020}\u{0A9B}\u{0AC7}\u{0020}\u{0A95}\u{0AC7}\u{0020}\u{0AA8}\u{0AB9}\u{0AC0}\u{0A82}\u{0020}\u{0AA4}\u{0AC7}\u{0020}\u{0AB8}\u{0A82}\u{0AAF}\u{0ACB}\u{0A9C}\u{0A95}\u{0AA8}\u{0AC7}\u{0020}\u{0AAA}\u{0AC2}\u{0A9B}\u{0ACB}\u{0020}\u{2014}\u{0020}\u{0A95}\u{0ACD}\u{0AAF}\u{0ABE}\u{0AB0}\u{0AC7}\u{0A95}\u{0020}\u{0A8F}\u{0020}\u{0A9C}\u{0020}\u{0A95}\u{0ABE}\u{0A97}\u{0AB3}\u{0AA8}\u{0ACB}\u{0020}\u{0AB8}\u{0AC7}\u{0A9F}\u{0020}\u{0AB5}\u{0ABE}\u{0AAA}\u{0AB0}\u{0AC0}\u{0020}\u{0AB6}\u{0A95}\u{0ABE}\u{0AAF}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}",
                'is_published' => true,
            ],
            [
                'module' => 'education',
                'slug' => 'study-support',
                'title_en' => "\u{004C}\u{0069}\u{0062}\u{0072}\u{0061}\u{0072}\u{0069}\u{0065}\u{0073}\u{002C}\u{0020}\u{0063}\u{006F}\u{0061}\u{0063}\u{0068}\u{0069}\u{006E}\u{0067}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0061}\u{0020}\u{0071}\u{0075}\u{0065}\u{0073}\u{0074}\u{0069}\u{006F}\u{006E}\u{0020}\u{0074}\u{006F}\u{0020}\u{0061}\u{0020}\u{006D}\u{0065}\u{006E}\u{0074}\u{006F}\u{0072}",
                'title_gu' => "\u{0AAA}\u{0AC1}\u{0AB8}\u{0ACD}\u{0AA4}\u{0A95}\u{0ABE}\u{0AB2}\u{0AAF}\u{002C}\u{0020}\u{0A95}\u{0ACB}\u{0A9A}\u{0ABF}\u{0A82}\u{0A97}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0AAE}\u{0ABE}\u{0AB0}\u{0ACD}\u{0A97}\u{0AA6}\u{0AB0}\u{0ACD}\u{0AB6}\u{0A95}\u{0AA8}\u{0AC7}\u{0020}\u{0AAA}\u{0ACD}\u{0AB0}\u{0AB6}\u{0ACD}\u{0AA8}",
                'excerpt_en' => "\u{0053}\u{0074}\u{0075}\u{0064}\u{0079}\u{0020}\u{0073}\u{0075}\u{0070}\u{0070}\u{006F}\u{0072}\u{0074}\u{0020}\u{0069}\u{0073}\u{0020}\u{0066}\u{006F}\u{0072}\u{0020}\u{0073}\u{0063}\u{0068}\u{006F}\u{006F}\u{006C}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0066}\u{0069}\u{0072}\u{0073}\u{0074}\u{002D}\u{0067}\u{0065}\u{006E}\u{0065}\u{0072}\u{0061}\u{0074}\u{0069}\u{006F}\u{006E}\u{0020}\u{0063}\u{006F}\u{006C}\u{006C}\u{0065}\u{0067}\u{0065}\u{0020}\u{0073}\u{0074}\u{0075}\u{0064}\u{0065}\u{006E}\u{0074}\u{0073}\u{002E}\u{0020}\u{0059}\u{006F}\u{0075}\u{0020}\u{0063}\u{0061}\u{006E}\u{0020}\u{0062}\u{006F}\u{0072}\u{0072}\u{006F}\u{0077}\u{0020}\u{0062}\u{006F}\u{006F}\u{006B}\u{0073}\u{002C}\u{0020}\u{0073}\u{0069}\u{0074}\u{0020}\u{0061}\u{0020}\u{0063}\u{006F}\u{0061}\u{0063}\u{0068}\u{0069}\u{006E}\u{0067}\u{0020}\u{0073}\u{006C}\u{006F}\u{0074}\u{002C}\u{0020}\u{006F}\u{0072}\u{0020}\u{0061}\u{0073}\u{006B}\u{0020}\u{0061}\u{0020}\u{006D}\u{0065}\u{006E}\u{0074}\u{006F}\u{0072}\u{0020}\u{0066}\u{0072}\u{006F}\u{006D}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0073}\u{0061}\u{006D}\u{0065}\u{0020}\u{0063}\u{0061}\u{0073}\u{0065}\u{0020}\u{0073}\u{006F}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0061}\u{006E}\u{0073}\u{0077}\u{0065}\u{0072}\u{0020}\u{0073}\u{0074}\u{0061}\u{0079}\u{0073}\u{0020}\u{006F}\u{006E}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0074}\u{0069}\u{006D}\u{0065}\u{006C}\u{0069}\u{006E}\u{0065}\u{002E}",
                'excerpt_gu' => "\u{0A86}\u{0020}\u{0AB8}\u{0AB9}\u{0ABE}\u{0AAF}\u{0020}\u{0AB6}\u{0ABE}\u{0AB3}\u{0ABE}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0AAA}\u{0AB9}\u{0AC7}\u{0AB2}\u{0AC0}\u{0020}\u{0AAA}\u{0AC7}\u{0AA2}\u{0AC0}\u{0AA8}\u{0ABE}\u{0020}\u{0A95}\u{0ACB}\u{0AB2}\u{0AC7}\u{0A9C}\u{0020}\u{0AB5}\u{0ABF}\u{0AA6}\u{0ACD}\u{0AAF}\u{0ABE}\u{0AB0}\u{0ACD}\u{0AA5}\u{0AC0}\u{0A93}\u{0020}\u{0AAE}\u{0ABE}\u{0A9F}\u{0AC7}\u{0020}\u{0A9B}\u{0AC7}\u{002E}\u{0020}\u{0AAA}\u{0AC1}\u{0AB8}\u{0ACD}\u{0AA4}\u{0A95}\u{0020}\u{0AB2}\u{0A88}\u{0020}\u{0AB6}\u{0A95}\u{0ACB}\u{002C}\u{0020}\u{0A95}\u{0ACB}\u{0A9A}\u{0ABF}\u{0A82}\u{0A97}\u{0020}\u{0AAC}\u{0AC7}\u{0AB8}\u{0AC0}\u{0020}\u{0AB6}\u{0A95}\u{0ACB}\u{002C}\u{0020}\u{0A85}\u{0AA5}\u{0AB5}\u{0ABE}\u{0020}\u{0A8F}\u{0020}\u{0A9C}\u{0020}\u{0A95}\u{0AC7}\u{0AB8}\u{0AAE}\u{0ABE}\u{0A82}\u{0AA5}\u{0AC0}\u{0020}\u{0AAE}\u{0ABE}\u{0AB0}\u{0ACD}\u{0A97}\u{0AA6}\u{0AB0}\u{0ACD}\u{0AB6}\u{0A95}\u{0AA8}\u{0AC7}\u{0020}\u{0AAA}\u{0AC2}\u{0A9B}\u{0AC0}\u{0020}\u{0AB6}\u{0A95}\u{0ACB}\u{0020}\u{0A9C}\u{0AC7}\u{0AA5}\u{0AC0}\u{0020}\u{0A9C}\u{0AB5}\u{0ABE}\u{0AAC}\u{0020}\u{0A9F}\u{0ABE}\u{0A88}\u{0AAE}\u{0AB2}\u{0ABE}\u{0A87}\u{0AA8}\u{0020}\u{0AAA}\u{0AB0}\u{0020}\u{0AB0}\u{0AB9}\u{0AC7}\u{002E}",
                'body_en' => "\u{003C}\u{0070}\u{003E}\u{0057}\u{0072}\u{0069}\u{0074}\u{0065}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0063}\u{006C}\u{0061}\u{0073}\u{0073}\u{002C}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0065}\u{0078}\u{0061}\u{006D}\u{0020}\u{0079}\u{006F}\u{0075}\u{0020}\u{0061}\u{0072}\u{0065}\u{0020}\u{0061}\u{0069}\u{006D}\u{0069}\u{006E}\u{0067}\u{0020}\u{0066}\u{006F}\u{0072}\u{002C}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0077}\u{0068}\u{0065}\u{0074}\u{0068}\u{0065}\u{0072}\u{0020}\u{0079}\u{006F}\u{0075}\u{0020}\u{0063}\u{0061}\u{006E}\u{0020}\u{0074}\u{0072}\u{0061}\u{0076}\u{0065}\u{006C}\u{0020}\u{0074}\u{006F}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{006E}\u{0065}\u{0061}\u{0072}\u{0065}\u{0073}\u{0074}\u{0020}\u{0063}\u{0065}\u{006E}\u{0074}\u{0072}\u{0065}\u{0020}\u{006F}\u{006E}\u{0063}\u{0065}\u{0020}\u{0061}\u{0020}\u{0077}\u{0065}\u{0065}\u{006B}\u{002E}\u{0020}\u{004D}\u{0065}\u{006E}\u{0074}\u{006F}\u{0072}\u{0073}\u{0020}\u{0072}\u{0065}\u{0070}\u{006C}\u{0079}\u{0020}\u{006F}\u{006E}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0063}\u{0061}\u{0073}\u{0065}\u{002C}\u{0020}\u{006E}\u{006F}\u{0074}\u{0020}\u{006F}\u{006E}\u{0020}\u{0061}\u{0020}\u{0070}\u{0072}\u{0069}\u{0076}\u{0061}\u{0074}\u{0065}\u{0020}\u{0063}\u{0068}\u{0061}\u{0074}\u{002C}\u{0020}\u{0073}\u{006F}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0066}\u{0061}\u{006D}\u{0069}\u{006C}\u{0079}\u{0020}\u{0063}\u{0061}\u{006E}\u{0020}\u{0073}\u{0065}\u{0065}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{006E}\u{0065}\u{0078}\u{0074}\u{0020}\u{0073}\u{0074}\u{0065}\u{0070}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}\u{003C}\u{0070}\u{003E}\u{0049}\u{0066}\u{0020}\u{0061}\u{0020}\u{0062}\u{006F}\u{006F}\u{006B}\u{0020}\u{0069}\u{0073}\u{0020}\u{006F}\u{0075}\u{0074}\u{002C}\u{0020}\u{0077}\u{0065}\u{0020}\u{0070}\u{0075}\u{0074}\u{0020}\u{0079}\u{006F}\u{0075}\u{0020}\u{006F}\u{006E}\u{0020}\u{0061}\u{0020}\u{0076}\u{0069}\u{006C}\u{006C}\u{0061}\u{0067}\u{0065}\u{0020}\u{0077}\u{0061}\u{0069}\u{0074}\u{0069}\u{006E}\u{0067}\u{0020}\u{006C}\u{0069}\u{0073}\u{0074}\u{0020}\u{0069}\u{006E}\u{0073}\u{0074}\u{0065}\u{0061}\u{0064}\u{0020}\u{006F}\u{0066}\u{0020}\u{0073}\u{0065}\u{006E}\u{0064}\u{0069}\u{006E}\u{0067}\u{0020}\u{0079}\u{006F}\u{0075}\u{0020}\u{0074}\u{006F}\u{0020}\u{0074}\u{006F}\u{0077}\u{006E}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}",
                'body_gu' => "\u{003C}\u{0070}\u{003E}\u{0AA7}\u{0ACB}\u{0AB0}\u{0AA3}\u{002C}\u{0020}\u{0A95}\u{0A88}\u{0020}\u{0AAA}\u{0AB0}\u{0AC0}\u{0A95}\u{0ACD}\u{0AB7}\u{0ABE}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0A85}\u{0AA0}\u{0AB5}\u{0ABE}\u{0AA1}\u{0ABF}\u{0AAF}\u{0AC7}\u{0020}\u{0A8F}\u{0A95}\u{0020}\u{0AB5}\u{0ABE}\u{0AB0}\u{0020}\u{0A95}\u{0AC7}\u{0AA8}\u{0ACD}\u{0AA6}\u{0ACD}\u{0AB0}\u{0020}\u{0A9C}\u{0A88}\u{0020}\u{0AB6}\u{0A95}\u{0ACB}\u{0020}\u{0A95}\u{0AC7}\u{0020}\u{0AA8}\u{0AB9}\u{0AC0}\u{0A82}\u{0020}\u{0AA4}\u{0AC7}\u{0020}\u{0AB2}\u{0A96}\u{0ACB}\u{002E}\u{0020}\u{0AAE}\u{0ABE}\u{0AB0}\u{0ACD}\u{0A97}\u{0AA6}\u{0AB0}\u{0ACD}\u{0AB6}\u{0A95}\u{0020}\u{0A95}\u{0AC7}\u{0AB8}\u{0020}\u{0AAA}\u{0AB0}\u{0020}\u{0A9C}\u{0AB5}\u{0ABE}\u{0AAC}\u{0020}\u{0A86}\u{0AAA}\u{0AC7}\u{0020}\u{0A9B}\u{0AC7}\u{002C}\u{0020}\u{0A96}\u{0ABE}\u{0AA8}\u{0A97}\u{0AC0}\u{0020}\u{0A9A}\u{0AC7}\u{0A9F}\u{0020}\u{0AAA}\u{0AB0}\u{0020}\u{0AA8}\u{0AB9}\u{0AC0}\u{0A82}\u{002C}\u{0020}\u{0A9C}\u{0AC7}\u{0AA5}\u{0AC0}\u{0020}\u{0AAA}\u{0AB0}\u{0ABF}\u{0AB5}\u{0ABE}\u{0AB0}\u{0020}\u{0A86}\u{0A97}\u{0AB3}\u{0AA8}\u{0AC1}\u{0A82}\u{0020}\u{0AAA}\u{0A97}\u{0AB2}\u{0AC1}\u{0A82}\u{0020}\u{0A9C}\u{0AC1}\u{0A8F}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}\u{003C}\u{0070}\u{003E}\u{0AAA}\u{0AC1}\u{0AB8}\u{0ACD}\u{0AA4}\u{0A95}\u{0020}\u{0AA8}\u{0020}\u{0AB9}\u{0ACB}\u{0AAF}\u{0020}\u{0AA4}\u{0ACB}\u{0020}\u{0AB6}\u{0AB9}\u{0AC7}\u{0AB0}\u{0020}\u{0AAE}\u{0ACB}\u{0A95}\u{0AB2}\u{0AA4}\u{0ABE}\u{0020}\u{0AAA}\u{0AB9}\u{0AC7}\u{0AB2}\u{0ABE}\u{0A82}\u{0020}\u{0A97}\u{0ABE}\u{0AAE}\u{0AA8}\u{0AC0}\u{0020}\u{0AB0}\u{0ABE}\u{0AB9}\u{0020}\u{0A9C}\u{0ACB}\u{0AB5}\u{0ABE}\u{0AA8}\u{0AC0}\u{0020}\u{0AAF}\u{0ABE}\u{0AA6}\u{0AC0}\u{0AAE}\u{0ABE}\u{0A82}\u{0020}\u{0AA8}\u{0ABE}\u{0AAE}\u{0020}\u{0AB2}\u{0A96}\u{0AC0}\u{0A8F}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}",
                'is_published' => true,
            ],
            [
                'module' => 'health',
                'slug' => 'camps-and-referrals',
                'title_en' => "\u{0043}\u{0061}\u{006D}\u{0070}\u{0073}\u{002C}\u{0020}\u{0073}\u{0063}\u{0072}\u{0065}\u{0065}\u{006E}\u{0069}\u{006E}\u{0067}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0077}\u{0061}\u{006C}\u{006B}\u{0069}\u{006E}\u{0067}\u{0020}\u{0077}\u{0069}\u{0074}\u{0068}\u{0020}\u{0061}\u{0020}\u{0076}\u{006F}\u{006C}\u{0075}\u{006E}\u{0074}\u{0065}\u{0065}\u{0072}",
                'title_gu' => "\u{0A95}\u{0AC7}\u{0AAE}\u{0ACD}\u{0AAA}\u{002C}\u{0020}\u{0AA4}\u{0AAA}\u{0ABE}\u{0AB8}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0AB8}\u{0ACD}\u{0AB5}\u{0AAF}\u{0A82}\u{0AB8}\u{0AC7}\u{0AB5}\u{0A95}\u{0020}\u{0AB8}\u{0ABE}\u{0AA5}\u{0AC7}\u{0020}\u{0A9A}\u{0ABE}\u{0AB2}\u{0AB5}\u{0AC1}\u{0A82}",
                'excerpt_en' => "\u{004D}\u{006F}\u{0062}\u{0069}\u{006C}\u{0065}\u{0020}\u{0063}\u{0061}\u{006D}\u{0070}\u{0073}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0073}\u{0069}\u{0063}\u{006B}\u{006C}\u{0065}\u{002D}\u{0063}\u{0065}\u{006C}\u{006C}\u{0020}\u{0073}\u{0063}\u{0072}\u{0065}\u{0065}\u{006E}\u{0069}\u{006E}\u{0067}\u{0020}\u{0061}\u{0072}\u{0065}\u{0020}\u{0070}\u{006F}\u{0073}\u{0074}\u{0065}\u{0064}\u{0020}\u{0068}\u{0065}\u{0072}\u{0065}\u{0020}\u{0077}\u{0068}\u{0065}\u{006E}\u{0020}\u{0061}\u{0020}\u{0064}\u{0061}\u{0074}\u{0065}\u{0020}\u{0069}\u{0073}\u{0020}\u{0066}\u{0069}\u{0078}\u{0065}\u{0064}\u{002E}\u{0020}\u{0049}\u{0066}\u{0020}\u{0061}\u{0020}\u{0066}\u{0061}\u{006D}\u{0069}\u{006C}\u{0079}\u{0020}\u{006E}\u{0065}\u{0065}\u{0064}\u{0073}\u{0020}\u{0061}\u{0020}\u{0072}\u{0065}\u{0066}\u{0065}\u{0072}\u{0072}\u{0061}\u{006C}\u{0020}\u{0074}\u{006F}\u{0020}\u{0061}\u{0020}\u{0063}\u{006C}\u{0069}\u{006E}\u{0069}\u{0063}\u{002C}\u{0020}\u{0061}\u{0070}\u{0070}\u{006C}\u{0079}\u{0020}\u{0073}\u{006F}\u{0020}\u{0061}\u{0020}\u{0076}\u{006F}\u{006C}\u{0075}\u{006E}\u{0074}\u{0065}\u{0065}\u{0072}\u{0020}\u{0063}\u{0061}\u{006E}\u{0020}\u{0077}\u{0061}\u{006C}\u{006B}\u{0020}\u{0077}\u{0069}\u{0074}\u{0068}\u{0020}\u{0074}\u{0068}\u{0065}\u{006D}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{006B}\u{0065}\u{0065}\u{0070}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0073}\u{0061}\u{006D}\u{0065}\u{0020}\u{0063}\u{0061}\u{0073}\u{0065}\u{0020}\u{006E}\u{0075}\u{006D}\u{0062}\u{0065}\u{0072}\u{002E}",
                'excerpt_gu' => "\u{0AAE}\u{0ACB}\u{0AAC}\u{0ABE}\u{0A87}\u{0AB2}\u{0020}\u{0A95}\u{0AC7}\u{0AAE}\u{0ACD}\u{0AAA}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0AB8}\u{0ABF}\u{0A95}\u{0AB2}\u{002D}\u{0AB8}\u{0AC7}\u{0AB2}\u{0020}\u{0AA4}\u{0AAA}\u{0ABE}\u{0AB8}\u{0AA8}\u{0AC0}\u{0020}\u{0AA4}\u{0ABE}\u{0AB0}\u{0AC0}\u{0A96}\u{0020}\u{0AA8}\u{0A95}\u{0ACD}\u{0A95}\u{0AC0}\u{0020}\u{0AA5}\u{0ABE}\u{0AAF}\u{0020}\u{0AA4}\u{0ACD}\u{0AAF}\u{0ABE}\u{0AB0}\u{0AC7}\u{0020}\u{0A85}\u{0AB9}\u{0AC0}\u{0A82}\u{0020}\u{0AAE}\u{0AC2}\u{0A95}\u{0ABE}\u{0AAF}\u{0020}\u{0A9B}\u{0AC7}\u{002E}\u{0020}\u{0A95}\u{0ACD}\u{0AB2}\u{0ABF}\u{0AA8}\u{0ABF}\u{0A95}\u{0020}\u{0AB0}\u{0AC7}\u{0AAB}\u{0AB0}\u{0AB2}\u{0020}\u{0A9C}\u{0ACB}\u{0A88}\u{0A8F}\u{0020}\u{0AA4}\u{0ACB}\u{0020}\u{0A85}\u{0AB0}\u{0A9C}\u{0AC0}\u{0020}\u{0A95}\u{0AB0}\u{0ACB}\u{0020}\u{0A9C}\u{0AC7}\u{0AA5}\u{0AC0}\u{0020}\u{0AB8}\u{0ACD}\u{0AB5}\u{0AAF}\u{0A82}\u{0AB8}\u{0AC7}\u{0AB5}\u{0A95}\u{0020}\u{0AB8}\u{0ABE}\u{0AA5}\u{0AC7}\u{0020}\u{0A9A}\u{0ABE}\u{0AB2}\u{0AC7}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0A8F}\u{0020}\u{0A9C}\u{0020}\u{0A95}\u{0AC7}\u{0AB8}\u{0020}\u{0AA8}\u{0A82}\u{0AAC}\u{0AB0}\u{0020}\u{0AB0}\u{0AB9}\u{0AC7}\u{002E}",
                'body_en' => "\u{003C}\u{0070}\u{003E}\u{0043}\u{0061}\u{0072}\u{0072}\u{0079}\u{0020}\u{0070}\u{0072}\u{0065}\u{0076}\u{0069}\u{006F}\u{0075}\u{0073}\u{0020}\u{0072}\u{0065}\u{0070}\u{006F}\u{0072}\u{0074}\u{0073}\u{0020}\u{0069}\u{0066}\u{0020}\u{0079}\u{006F}\u{0075}\u{0020}\u{0068}\u{0061}\u{0076}\u{0065}\u{0020}\u{0074}\u{0068}\u{0065}\u{006D}\u{002E}\u{0020}\u{0054}\u{0068}\u{0065}\u{0020}\u{0063}\u{0061}\u{006D}\u{0070}\u{0020}\u{0064}\u{006F}\u{0065}\u{0073}\u{0020}\u{006E}\u{006F}\u{0074}\u{0020}\u{0072}\u{0065}\u{0070}\u{006C}\u{0061}\u{0063}\u{0065}\u{0020}\u{0061}\u{0020}\u{0068}\u{006F}\u{0073}\u{0070}\u{0069}\u{0074}\u{0061}\u{006C}\u{003B}\u{0020}\u{0069}\u{0074}\u{0020}\u{0069}\u{0073}\u{0020}\u{0061}\u{0020}\u{0066}\u{0069}\u{0072}\u{0073}\u{0074}\u{0020}\u{0063}\u{0068}\u{0065}\u{0063}\u{006B}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0061}\u{0020}\u{006C}\u{0065}\u{0074}\u{0074}\u{0065}\u{0072}\u{0020}\u{0066}\u{006F}\u{0072}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{006E}\u{0065}\u{0078}\u{0074}\u{0020}\u{0076}\u{0069}\u{0073}\u{0069}\u{0074}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}\u{003C}\u{0070}\u{003E}\u{0042}\u{006C}\u{006F}\u{006F}\u{0064}\u{0020}\u{0072}\u{0065}\u{0071}\u{0075}\u{0065}\u{0073}\u{0074}\u{0073}\u{0020}\u{0061}\u{0072}\u{0065}\u{0020}\u{0061}\u{0020}\u{006E}\u{0065}\u{0069}\u{0067}\u{0068}\u{0062}\u{006F}\u{0075}\u{0072}\u{002D}\u{0074}\u{006F}\u{002D}\u{006E}\u{0065}\u{0069}\u{0067}\u{0068}\u{0062}\u{006F}\u{0075}\u{0072}\u{0020}\u{0063}\u{0061}\u{006C}\u{006C}\u{002C}\u{0020}\u{006E}\u{006F}\u{0074}\u{0020}\u{0061}\u{0020}\u{0062}\u{006C}\u{006F}\u{006F}\u{0064}\u{0020}\u{0062}\u{0061}\u{006E}\u{006B}\u{002E}\u{0020}\u{0057}\u{0072}\u{0069}\u{0074}\u{0065}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0067}\u{0072}\u{006F}\u{0075}\u{0070}\u{002C}\u{0020}\u{0075}\u{006E}\u{0069}\u{0074}\u{0073}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0061}\u{0020}\u{0070}\u{0068}\u{006F}\u{006E}\u{0065}\u{0020}\u{0074}\u{0068}\u{0061}\u{0074}\u{0020}\u{0073}\u{0074}\u{0061}\u{0079}\u{0073}\u{0020}\u{006F}\u{006E}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}",
                'body_gu' => "\u{003C}\u{0070}\u{003E}\u{0A85}\u{0A97}\u{0ABE}\u{0A89}\u{0AA8}\u{0ABE}\u{0020}\u{0AB0}\u{0ABF}\u{0AAA}\u{0ACB}\u{0AB0}\u{0ACD}\u{0A9F}\u{0020}\u{0AB9}\u{0ACB}\u{0AAF}\u{0020}\u{0AA4}\u{0ACB}\u{0020}\u{0AB2}\u{0ABE}\u{0AB5}\u{0ACB}\u{002E}\u{0020}\u{0A95}\u{0AC7}\u{0AAE}\u{0ACD}\u{0AAA}\u{0020}\u{0AB9}\u{0ACB}\u{0AB8}\u{0ACD}\u{0AAA}\u{0ABF}\u{0A9F}\u{0AB2}\u{0AA8}\u{0AC1}\u{0A82}\u{0020}\u{0AB8}\u{0ACD}\u{0AA5}\u{0ABE}\u{0AA8}\u{0020}\u{0AA8}\u{0AA5}\u{0AC0}\u{0020}\u{0AB2}\u{0AC7}\u{0AA4}\u{0ACB}\u{003B}\u{0020}\u{0AAA}\u{0AB9}\u{0AC7}\u{0AB2}\u{0AC0}\u{0020}\u{0AA4}\u{0AAA}\u{0ABE}\u{0AB8}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0A86}\u{0A97}\u{0AB2}\u{0AC0}\u{0020}\u{0AAE}\u{0AC1}\u{0AB2}\u{0ABE}\u{0A95}\u{0ABE}\u{0AA4}\u{0AA8}\u{0ACB}\u{0020}\u{0AAA}\u{0AA4}\u{0ACD}\u{0AB0}\u{0020}\u{0A9B}\u{0AC7}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}\u{003C}\u{0070}\u{003E}\u{0AB0}\u{0A95}\u{0ACD}\u{0AA4}\u{0020}\u{0AB5}\u{0ABF}\u{0AA8}\u{0A82}\u{0AA4}\u{0AC0}\u{0020}\u{0AAA}\u{0AA1}\u{0ACB}\u{0AB6}\u{0AC0}\u{0AA8}\u{0AC0}\u{0020}\u{0AB9}\u{0ABE}\u{0A95}\u{0AB2}\u{0020}\u{0A9B}\u{0AC7}\u{002C}\u{0020}\u{0AAC}\u{0ACD}\u{0AB2}\u{0AA1}\u{0020}\u{0AAC}\u{0AC7}\u{0A82}\u{0A95}\u{0020}\u{0AA8}\u{0AB9}\u{0AC0}\u{0A82}\u{002E}\u{0020}\u{0A97}\u{0ACD}\u{0AB0}\u{0AC1}\u{0AAA}\u{002C}\u{0020}\u{0AAF}\u{0AC1}\u{0AA8}\u{0ABF}\u{0A9F}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0A9A}\u{0ABE}\u{0AB2}\u{0AC1}\u{0020}\u{0AAB}\u{0ACB}\u{0AA8}\u{0020}\u{0AB2}\u{0A96}\u{0ACB}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}",
                'is_published' => true,
            ],
            [
                'module' => 'sakhi',
                'slug' => 'circle-meetings',
                'title_en' => "\u{0053}\u{0061}\u{0076}\u{0069}\u{006E}\u{0067}\u{0073}\u{002C}\u{0020}\u{0061}\u{0020}\u{0073}\u{006D}\u{0061}\u{006C}\u{006C}\u{0020}\u{0073}\u{0068}\u{006F}\u{0070}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0061}\u{0020}\u{006A}\u{006F}\u{0069}\u{006E}\u{0074}\u{0020}\u{0066}\u{006F}\u{0072}\u{006D}",
                'title_gu' => "\u{0AAC}\u{0A9A}\u{0AA4}\u{002C}\u{0020}\u{0AA8}\u{0ABE}\u{0AA8}\u{0AC1}\u{0A82}\u{0020}\u{0AA6}\u{0AC1}\u{0A95}\u{0ABE}\u{0AA8}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0AB8}\u{0ABE}\u{0A9D}\u{0AC1}\u{0A82}\u{0020}\u{0AAB}\u{0ACB}\u{0AB0}\u{0ACD}\u{0AAE}",
                'excerpt_en' => "\u{0053}\u{0061}\u{006B}\u{0068}\u{0069}\u{0020}\u{0063}\u{0069}\u{0072}\u{0063}\u{006C}\u{0065}\u{0073}\u{0020}\u{006D}\u{0065}\u{0065}\u{0074}\u{0020}\u{0066}\u{006F}\u{0072}\u{0020}\u{0073}\u{0061}\u{0076}\u{0069}\u{006E}\u{0067}\u{0073}\u{002C}\u{0020}\u{0061}\u{0020}\u{0073}\u{0068}\u{006F}\u{0070}\u{0020}\u{0069}\u{0064}\u{0065}\u{0061}\u{002C}\u{0020}\u{006F}\u{0072}\u{0020}\u{006F}\u{006E}\u{0065}\u{0020}\u{006A}\u{006F}\u{0069}\u{006E}\u{0074}\u{0020}\u{0061}\u{0070}\u{0070}\u{006C}\u{0069}\u{0063}\u{0061}\u{0074}\u{0069}\u{006F}\u{006E}\u{002E}\u{0020}\u{0054}\u{0068}\u{0065}\u{0020}\u{0061}\u{0067}\u{0065}\u{0076}\u{0061}\u{006E}\u{0020}\u{0063}\u{0061}\u{006E}\u{0020}\u{0073}\u{0065}\u{006E}\u{0064}\u{0020}\u{0061}\u{0020}\u{0072}\u{0065}\u{0071}\u{0075}\u{0065}\u{0073}\u{0074}\u{0020}\u{0077}\u{0068}\u{0065}\u{006E}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0067}\u{0072}\u{006F}\u{0075}\u{0070}\u{0020}\u{006E}\u{0065}\u{0065}\u{0064}\u{0073}\u{0020}\u{0061}\u{0020}\u{0066}\u{006F}\u{0072}\u{006D}\u{0020}\u{006F}\u{0072}\u{0020}\u{0061}\u{0020}\u{006D}\u{0061}\u{0072}\u{006B}\u{0065}\u{0074}\u{0020}\u{006C}\u{0069}\u{006E}\u{006B}\u{002E}\u{0020}\u{0054}\u{0068}\u{0065}\u{0020}\u{0063}\u{0061}\u{0073}\u{0065}\u{0020}\u{0062}\u{0065}\u{006C}\u{006F}\u{006E}\u{0067}\u{0073}\u{0020}\u{0074}\u{006F}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0063}\u{0069}\u{0072}\u{0063}\u{006C}\u{0065}\u{002C}\u{0020}\u{006E}\u{006F}\u{0074}\u{0020}\u{0074}\u{006F}\u{0020}\u{006F}\u{006E}\u{0065}\u{0020}\u{0070}\u{0065}\u{0072}\u{0073}\u{006F}\u{006E}\u{002E}",
                'excerpt_gu' => "\u{0AB8}\u{0A96}\u{0AC0}\u{0020}\u{0AAE}\u{0A82}\u{0AA1}\u{0AB3}\u{0020}\u{0AAC}\u{0A9A}\u{0AA4}\u{002C}\u{0020}\u{0AA6}\u{0AC1}\u{0A95}\u{0ABE}\u{0AA8}\u{0AA8}\u{0ABE}\u{0020}\u{0AB5}\u{0ABF}\u{0A9A}\u{0ABE}\u{0AB0}\u{0020}\u{0A85}\u{0AA5}\u{0AB5}\u{0ABE}\u{0020}\u{0A8F}\u{0A95}\u{0020}\u{0AB8}\u{0ABE}\u{0A9D}\u{0AC0}\u{0020}\u{0A85}\u{0AB0}\u{0A9C}\u{0AC0}\u{0020}\u{0AAE}\u{0ABE}\u{0A9F}\u{0AC7}\u{0020}\u{0AAE}\u{0AB3}\u{0AC7}\u{0020}\u{0A9B}\u{0AC7}\u{002E}\u{0020}\u{0A9C}\u{0AC2}\u{0AA5}\u{0AA8}\u{0AC7}\u{0020}\u{0AAB}\u{0ACB}\u{0AB0}\u{0ACD}\u{0AAE}\u{0020}\u{0A95}\u{0AC7}\u{0020}\u{0AAC}\u{0A9C}\u{0ABE}\u{0AB0}\u{0020}\u{0A9C}\u{0ACB}\u{0AA1}\u{0ABE}\u{0AA3}\u{0020}\u{0A9C}\u{0ACB}\u{0A88}\u{0A8F}\u{0020}\u{0AA4}\u{0ACD}\u{0AAF}\u{0ABE}\u{0AB0}\u{0AC7}\u{0020}\u{0A86}\u{0A97}\u{0AC7}\u{0AB5}\u{0ABE}\u{0AA8}\u{0020}\u{0AB5}\u{0ABF}\u{0AA8}\u{0A82}\u{0AA4}\u{0AC0}\u{0020}\u{0AAE}\u{0ACB}\u{0A95}\u{0AB2}\u{0AC7}\u{002E}\u{0020}\u{0A95}\u{0AC7}\u{0AB8}\u{0020}\u{0AAE}\u{0A82}\u{0AA1}\u{0AB3}\u{0AA8}\u{0ACB}\u{0020}\u{0A9B}\u{0AC7}\u{002C}\u{0020}\u{0A8F}\u{0A95}\u{0020}\u{0AB5}\u{0ACD}\u{0AAF}\u{0A95}\u{0ACD}\u{0AA4}\u{0ABF}\u{0AA8}\u{0ACB}\u{0020}\u{0AA8}\u{0AB9}\u{0AC0}\u{0A82}\u{002E}",
                'body_en' => "\u{003C}\u{0070}\u{003E}\u{0057}\u{0072}\u{0069}\u{0074}\u{0065}\u{0020}\u{0068}\u{006F}\u{0077}\u{0020}\u{006D}\u{0061}\u{006E}\u{0079}\u{0020}\u{006D}\u{0065}\u{006D}\u{0062}\u{0065}\u{0072}\u{0073}\u{0020}\u{0073}\u{0069}\u{0074}\u{002C}\u{0020}\u{0077}\u{0068}\u{0061}\u{0074}\u{0020}\u{0074}\u{0068}\u{0065}\u{0079}\u{0020}\u{0073}\u{0061}\u{0076}\u{0065}\u{0020}\u{0065}\u{0061}\u{0063}\u{0068}\u{0020}\u{006D}\u{006F}\u{006E}\u{0074}\u{0068}\u{002C}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0077}\u{0068}\u{0065}\u{0074}\u{0068}\u{0065}\u{0072}\u{0020}\u{0074}\u{0068}\u{0065}\u{0079}\u{0020}\u{0061}\u{006C}\u{0072}\u{0065}\u{0061}\u{0064}\u{0079}\u{0020}\u{0068}\u{0061}\u{0076}\u{0065}\u{0020}\u{0061}\u{0020}\u{0067}\u{0072}\u{006F}\u{0075}\u{0070}\u{0020}\u{0061}\u{0063}\u{0063}\u{006F}\u{0075}\u{006E}\u{0074}\u{002E}\u{0020}\u{0057}\u{0065}\u{0020}\u{0068}\u{0065}\u{006C}\u{0070}\u{0020}\u{0077}\u{0069}\u{0074}\u{0068}\u{0020}\u{0061}\u{0020}\u{0073}\u{0069}\u{006D}\u{0070}\u{006C}\u{0065}\u{0020}\u{0070}\u{006C}\u{0061}\u{006E}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0061}\u{0020}\u{0064}\u{0061}\u{0074}\u{0065}\u{0020}\u{0066}\u{006F}\u{0072}\u{0020}\u{0074}\u{0072}\u{0061}\u{0069}\u{006E}\u{0069}\u{006E}\u{0067}\u{002C}\u{0020}\u{006E}\u{006F}\u{0074}\u{0020}\u{0061}\u{0020}\u{006C}\u{006F}\u{0061}\u{006E}\u{0020}\u{0073}\u{0068}\u{006F}\u{0070}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}",
                'body_gu' => "\u{003C}\u{0070}\u{003E}\u{0A95}\u{0AC7}\u{0A9F}\u{0AB2}\u{0ABE}\u{0020}\u{0AB8}\u{0AAD}\u{0ACD}\u{0AAF}\u{0020}\u{0AAC}\u{0AC7}\u{0AB8}\u{0AC7}\u{002C}\u{0020}\u{0AA6}\u{0AB0}\u{0020}\u{0AAE}\u{0AB9}\u{0ABF}\u{0AA8}\u{0AC7}\u{0020}\u{0AB6}\u{0AC1}\u{0A82}\u{0020}\u{0AAC}\u{0A9A}\u{0AA4}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0A9C}\u{0AC2}\u{0AA5}\u{0020}\u{0A96}\u{0ABE}\u{0AA4}\u{0AC1}\u{0A82}\u{0020}\u{0A9B}\u{0AC7}\u{0020}\u{0A95}\u{0AC7}\u{0020}\u{0AA8}\u{0AB9}\u{0AC0}\u{0A82}\u{0020}\u{0AA4}\u{0AC7}\u{0020}\u{0AB2}\u{0A96}\u{0ACB}\u{002E}\u{0020}\u{0AB8}\u{0AB0}\u{0AB3}\u{0020}\u{0AAF}\u{0ACB}\u{0A9C}\u{0AA8}\u{0ABE}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0AA4}\u{0ABE}\u{0AB2}\u{0AC0}\u{0AAE}\u{0AA8}\u{0AC0}\u{0020}\u{0AA4}\u{0ABE}\u{0AB0}\u{0AC0}\u{0A96}\u{0AAE}\u{0ABE}\u{0A82}\u{0020}\u{0AAE}\u{0AA6}\u{0AA6}\u{0020}\u{0A95}\u{0AB0}\u{0AC0}\u{0A8F}\u{002C}\u{0020}\u{0AB2}\u{0ACB}\u{0AA8}\u{0020}\u{0AA6}\u{0AC1}\u{0A95}\u{0ABE}\u{0AA8}\u{0020}\u{0AA8}\u{0AB9}\u{0AC0}\u{0A82}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}",
                'is_published' => true,
            ],
            [
                'module' => 'village_reports',
                'slug' => 'how-to-write-a-note',
                'title_en' => "\u{0057}\u{0061}\u{0074}\u{0065}\u{0072}\u{002C}\u{0020}\u{0072}\u{006F}\u{0061}\u{0064}\u{0073}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0070}\u{006F}\u{0077}\u{0065}\u{0072}\u{0020}\u{2014}\u{0020}\u{0077}\u{0072}\u{0069}\u{0074}\u{0065}\u{0020}\u{0061}\u{0020}\u{006E}\u{006F}\u{0074}\u{0065}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0074}\u{0061}\u{006C}\u{0075}\u{006B}\u{0061}\u{0020}\u{0063}\u{0061}\u{006E}\u{0020}\u{0063}\u{0061}\u{0072}\u{0072}\u{0079}",
                'title_gu' => "\u{0AAA}\u{0ABE}\u{0AA3}\u{0AC0}\u{002C}\u{0020}\u{0AB0}\u{0AB8}\u{0ACD}\u{0AA4}\u{0ABE}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0AB5}\u{0AC0}\u{0A9C}\u{0AB3}\u{0AC0}\u{0020}\u{2014}\u{0020}\u{0AA4}\u{0ABE}\u{0AB2}\u{0AC1}\u{0A95}\u{0ABE}\u{0020}\u{0AB2}\u{0A88}\u{0020}\u{0A9C}\u{0A88}\u{0020}\u{0AB6}\u{0A95}\u{0AC7}\u{0020}\u{0AA4}\u{0AC7}\u{0AB5}\u{0AC0}\u{0020}\u{0AA8}\u{0ACB}\u{0A82}\u{0AA7}",
                'excerpt_en' => "\u{0057}\u{0072}\u{0069}\u{0074}\u{0065}\u{0020}\u{0077}\u{0068}\u{0061}\u{0074}\u{0020}\u{0062}\u{0072}\u{006F}\u{006B}\u{0065}\u{002C}\u{0020}\u{0073}\u{0069}\u{006E}\u{0063}\u{0065}\u{0020}\u{0077}\u{0068}\u{0065}\u{006E}\u{002C}\u{0020}\u{0061}\u{006E}\u{0064}\u{0020}\u{0077}\u{0068}\u{006F}\u{0020}\u{0061}\u{006C}\u{0072}\u{0065}\u{0061}\u{0064}\u{0079}\u{0020}\u{0077}\u{0065}\u{006E}\u{0074}\u{0020}\u{0074}\u{006F}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0070}\u{0061}\u{006E}\u{0063}\u{0068}\u{0061}\u{0079}\u{0061}\u{0074}\u{002E}\u{0020}\u{0041}\u{0020}\u{0073}\u{0074}\u{0061}\u{0066}\u{0066}\u{0020}\u{006D}\u{0065}\u{006D}\u{0062}\u{0065}\u{0072}\u{0020}\u{0063}\u{0061}\u{006E}\u{0020}\u{0074}\u{0068}\u{0065}\u{006E}\u{0020}\u{0063}\u{0061}\u{0072}\u{0072}\u{0079}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0073}\u{0061}\u{006D}\u{0065}\u{0020}\u{006E}\u{006F}\u{0074}\u{0065}\u{0020}\u{0074}\u{006F}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0074}\u{0061}\u{006C}\u{0075}\u{006B}\u{0061}\u{0020}\u{0064}\u{0065}\u{0073}\u{006B}\u{0020}\u{0077}\u{0069}\u{0074}\u{0068}\u{006F}\u{0075}\u{0074}\u{0020}\u{0061}\u{0073}\u{006B}\u{0069}\u{006E}\u{0067}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0076}\u{0069}\u{006C}\u{006C}\u{0061}\u{0067}\u{0065}\u{0020}\u{0074}\u{006F}\u{0020}\u{0072}\u{0065}\u{0070}\u{0065}\u{0061}\u{0074}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0073}\u{0074}\u{006F}\u{0072}\u{0079}\u{002E}",
                'excerpt_gu' => "\u{0AB6}\u{0AC1}\u{0A82}\u{0020}\u{0AA4}\u{0AC2}\u{0A9F}\u{0ACD}\u{0AAF}\u{0AC1}\u{0A82}\u{002C}\u{0020}\u{0A95}\u{0ACD}\u{0AAF}\u{0ABE}\u{0AB0}\u{0AA5}\u{0AC0}\u{0020}\u{0A85}\u{0AA8}\u{0AC7}\u{0020}\u{0AAA}\u{0A82}\u{0A9A}\u{0ABE}\u{0AAF}\u{0AA4}\u{0AAE}\u{0ABE}\u{0A82}\u{0020}\u{0A95}\u{0ACB}\u{0AA3}\u{0020}\u{0A97}\u{0AAF}\u{0AC1}\u{0A82}\u{0020}\u{0AA4}\u{0AC7}\u{0020}\u{0AB2}\u{0A96}\u{0ACB}\u{002E}\u{0020}\u{0A95}\u{0AB0}\u{0ACD}\u{0AAE}\u{0A9A}\u{0ABE}\u{0AB0}\u{0AC0}\u{0020}\u{0A8F}\u{0020}\u{0A9C}\u{0020}\u{0AA8}\u{0ACB}\u{0A82}\u{0AA7}\u{0020}\u{0AA4}\u{0ABE}\u{0AB2}\u{0AC1}\u{0A95}\u{0ABE}\u{0020}\u{0AA1}\u{0AC7}\u{0AB8}\u{0ACD}\u{0A95}\u{0020}\u{0AB8}\u{0AC1}\u{0AA7}\u{0AC0}\u{0020}\u{0AB2}\u{0A88}\u{0020}\u{0AB6}\u{0A95}\u{0AC7}\u{002C}\u{0020}\u{0A97}\u{0ABE}\u{0AAE}\u{0AA8}\u{0AC7}\u{0020}\u{0AB5}\u{0ABE}\u{0AB0}\u{0ACD}\u{0AA4}\u{0ABE}\u{0020}\u{0AAB}\u{0AB0}\u{0AC0}\u{0020}\u{0A95}\u{0AB9}\u{0AC7}\u{0AB5}\u{0ABE}\u{0AA8}\u{0AC0}\u{0020}\u{0A9C}\u{0AB0}\u{0AC2}\u{0AB0}\u{0020}\u{0AA8}\u{0020}\u{0AAA}\u{0AA1}\u{0AC7}\u{002E}",
                'body_en' => "\u{003C}\u{0070}\u{003E}\u{0041}\u{0020}\u{0070}\u{0068}\u{006F}\u{0074}\u{006F}\u{0020}\u{006F}\u{0066}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0062}\u{0072}\u{006F}\u{006B}\u{0065}\u{006E}\u{0020}\u{0068}\u{0061}\u{006E}\u{0064}\u{0070}\u{0075}\u{006D}\u{0070}\u{0020}\u{006F}\u{0072}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0064}\u{0061}\u{0072}\u{006B}\u{0020}\u{006C}\u{0061}\u{006E}\u{0065}\u{0020}\u{0068}\u{0065}\u{006C}\u{0070}\u{0073}\u{002C}\u{0020}\u{0062}\u{0075}\u{0074}\u{0020}\u{0069}\u{0074}\u{0020}\u{0069}\u{0073}\u{0020}\u{006F}\u{0070}\u{0074}\u{0069}\u{006F}\u{006E}\u{0061}\u{006C}\u{002E}\u{0020}\u{0054}\u{0068}\u{0065}\u{0020}\u{0063}\u{0061}\u{0073}\u{0065}\u{0020}\u{006E}\u{0075}\u{006D}\u{0062}\u{0065}\u{0072}\u{0020}\u{0069}\u{0073}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0066}\u{0069}\u{006C}\u{0065}\u{002E}\u{0020}\u{0049}\u{0066}\u{0020}\u{0074}\u{0077}\u{006F}\u{0020}\u{0068}\u{0061}\u{006D}\u{006C}\u{0065}\u{0074}\u{0073}\u{0020}\u{0073}\u{0068}\u{0061}\u{0072}\u{0065}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0073}\u{0061}\u{006D}\u{0065}\u{0020}\u{0066}\u{0061}\u{0075}\u{006C}\u{0074}\u{002C}\u{0020}\u{0073}\u{0061}\u{0079}\u{0020}\u{0073}\u{006F}\u{0020}\u{006F}\u{006E}\u{0020}\u{0074}\u{0068}\u{0065}\u{0020}\u{0066}\u{006F}\u{0072}\u{006D}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}",
                'body_gu' => "\u{003C}\u{0070}\u{003E}\u{0AA4}\u{0AC2}\u{0A9F}\u{0AC7}\u{0AB2}\u{0ABE}\u{0020}\u{0AB9}\u{0AC7}\u{0AA8}\u{0ACD}\u{0AA1}\u{0AAA}\u{0AAE}\u{0ACD}\u{0AAA}\u{0020}\u{0A95}\u{0AC7}\u{0020}\u{0A85}\u{0A82}\u{0AA7}\u{0ABE}\u{0AB0}\u{0AC0}\u{0020}\u{0A97}\u{0AB2}\u{0AC0}\u{0AA8}\u{0ACB}\u{0020}\u{0AAB}\u{0ACB}\u{0A9F}\u{0ACB}\u{0020}\u{0AAE}\u{0AA6}\u{0AA6}\u{0020}\u{0A95}\u{0AB0}\u{0AC7}\u{002C}\u{0020}\u{0AAA}\u{0AA3}\u{0020}\u{0AAB}\u{0AB0}\u{0A9C}\u{0ABF}\u{0AAF}\u{0ABE}\u{0AA4}\u{0020}\u{0AA8}\u{0AA5}\u{0AC0}\u{002E}\u{0020}\u{0A95}\u{0AC7}\u{0AB8}\u{0020}\u{0AA8}\u{0A82}\u{0AAC}\u{0AB0}\u{0020}\u{0A8F}\u{0020}\u{0AAB}\u{0ABE}\u{0A87}\u{0AB2}\u{0020}\u{0A9B}\u{0AC7}\u{002E}\u{0020}\u{0AAC}\u{0AC7}\u{0020}\u{0AAB}\u{0AB3}\u{0ABF}\u{0AAF}\u{0ABE}\u{0AA8}\u{0AC0}\u{0020}\u{0A8F}\u{0020}\u{0A9C}\u{0020}\u{0A96}\u{0ABE}\u{0AAE}\u{0AC0}\u{0020}\u{0AB9}\u{0ACB}\u{0AAF}\u{0020}\u{0AA4}\u{0ACB}\u{0020}\u{0AAB}\u{0ACB}\u{0AB0}\u{0ACD}\u{0AAE}\u{0020}\u{0AAA}\u{0AB0}\u{0020}\u{0AB2}\u{0A96}\u{0ACB}\u{002E}\u{003C}\u{002F}\u{0070}\u{003E}",
                'is_published' => true,
            ],
        ];

        foreach ($items as $row) {
            $this->upsertItem($row['module'], $row['slug'], [
                'title_en' => $row['title_en'],
                'title_gu' => $row['title_gu'],
                'excerpt_en' => $row['excerpt_en'],
                'excerpt_gu' => $row['excerpt_gu'],
                'body_en' => $row['body_en'],
                'body_gu' => $row['body_gu'],
                'is_published' => true,
            ]);
        }
    }

    private function seedStaticPages(): void
    {
        $pages = [
            [
                'slug' => 'about-us',
                'title_en' => 'About GGVT',
                'title_gu' => 'અમારા વિશે',
                'body_en' => '<p>Global Gramin Vikas Trust (GGVT) works across tribal districts of Gujarat on education, legal rights, health and livelihoods.</p><p>Tribal Helping Hand is the public service desk of GGVT. Citizens can apply for help, track their case, and find schemes, scholarships and health camps in one place.</p>',
                'body_gu' => '<p>ગ્લોબલ ગ્રામીણ વિકાસ ટ્રસ્ટ (GGVT) ગુજરાતના આદિવાસી પટ્ટામાં શિક્ષણ, કાનૂની અધિકાર, આરોગ્ય અને આજીવિકા માટે કાર્યરત સંસ્થા છે.</p><p>ટ્રાઇબલ હેલ્પિંગ હેન્ડ GGVTની જનસેવા ડેસ્ક છે. નાગરિકો મદદ માટે અરજી કરી શકે, કેસ ટ્રેક કરી શકે અને યોજના જોઈ શકે છે.</p>',
            ],
            [
                'slug' => 'privacy-policy',
                'title_en' => 'Privacy Policy',
                'title_gu' => 'ગોપનીયતા નીતિ',
                'body_en' => '<p>We collect only the details needed to register and process a help request: name, phone, email, village and the description of the need.</p><p>Documents and personal data are used for coordination with government offices and field staff. We do not sell personal data. You may request correction or deletion of your account by contacting the helpline.</p>',
                'body_gu' => '<p>અમે મદદની અરજી નોંધવા માટે જરૂરી વિગતો જ એકત્રિત કરીએ છીએ: નામ, ફોન, ઇમેઇલ, ગામ અને જરૂરિયાતનું વર્ણન.</p><p>દસ્તાવેજો અને અંગત માહિતી સરકારી કચેરી અને ક્ષેત્રીય સ્ટાફ સાથે સંકલન માટે વપરાય છે. અમે અંગત માહિતી વેચતા નથી.</p>',
            ],
            [
                'slug' => 'terms-conditions',
                'title_en' => 'Terms and Conditions',
                'title_gu' => 'નિયમો અને શરતો',
                'body_en' => '<p>Use this portal with true information. False applications may be rejected and recorded in the case timeline.</p><p>GGVT provides facilitation, not a statutory guarantee of government scheme approval. Case status updates are sent to the email used at submission.</p>',
                'body_gu' => '<p>આ પોર્ટલનો ઉપયોગ સાચી માહિતી સાથે જ કરો. ખોટી અરજીઓ નકારી શકાય છે.</p><p>GGVT સહાય અને સંકલન આપે છે; સરકારી યોજનાની મંજૂરીની કાનૂની ખાતરી નથી. સ્થિતિ અપડેટ અરજીના ઇમેઇલ પર મોકલાય છે.</p>',
            ],
        ];

        foreach ($pages as $page) {
            StaticPage::updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'title_key' => $page['title_en'],
                    'content_key' => strip_tags($page['body_en']),
                    'title_en' => $page['title_en'],
                    'title_gu' => $page['title_gu'],
                    'body_en' => $page['body_en'],
                    'body_gu' => $page['body_gu'],
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedHomeBlocks(): void
    {
        $blocks = [
            [
                'slug' => 'hero',
                'title_en' => 'A helping hand for tribal families',
                'title_gu' => 'આદિવાસી પરિવારો માટે સહાયક હાથ',
                'excerpt_en' => 'Schemes, health, work and a case you can follow.',
                'excerpt_gu' => 'યોજના, આરોગ્ય, રોજગાર અને ટ્રેક કરી શકાય તેવી અરજી.',
                'body_en' => 'Sit with a coordinator, apply from a phone, and keep the same case number from the first visit to the last follow-up. We do not promise a government sanction.',
                'body_gu' => 'સંયોજક સાથે બેસો, ફોનથી અરજી કરો, અને એજ કેસ નંબર પહેલી મુલાકાતથી છેલ્લી ફોલો-અપ સુધી રાખો. અમે સરકારી મંજૂરીની ખાતરી નથી.',
            ],
            [
                'slug' => 'how-1',
                'title_en' => 'Choose the desk',
                'title_gu' => 'ડેસ્ક પસંદ કરો',
                'excerpt_en' => 'Open the service that matches the village need.',
                'excerpt_gu' => 'ગામની જરૂર સાથે મળતી સેવા ખોલો.',
                'body_en' => 'Each card is published by the field desk, not a generic directory.',
                'body_gu' => 'દરેક કાર્ડ ક્ષેત્ર ડેસ્ક પ્રકાશિત કરે છે, સામાન્ય ડિરેક્ટરી નહીં.',
            ],
            [
                'slug' => 'how-2',
                'title_en' => 'Tell us who to call',
                'title_gu' => 'કોને કોલ કરવું એ કહો',
                'excerpt_en' => 'Give a name, mobile and email.',
                'excerpt_gu' => 'નામ, મોબાઇલ અને ઇમેઇલ આપો.',
                'body_en' => 'If the email is new we create a citizen login and send the password.',
                'body_gu' => 'નવી ઇમેઇલ હોય તો નાગરિક લોગિન બને અને પાસવર્ડ મોકલાય.',
            ],
            [
                'slug' => 'how-3',
                'title_en' => 'Follow the same case',
                'title_gu' => 'એજ કેસ અનુસરો',
                'excerpt_en' => 'Keep the case number.',
                'excerpt_gu' => 'કેસ નંબર રાખો.',
                'body_en' => 'Track it here or on the phone with login or an OTP to the application email.',
                'body_gu' => 'વેબ અથવા ફોન પર લોગિન કે OTP થી ટ્રેક કરો.',
            ],
            [
                'slug' => 'papers',
                'title_en' => 'Papers you often need',
                'title_gu' => 'વારંવાર જોઈતા કાગળ',
                'excerpt_en' => 'Aadhaar, caste certificate, ration card, bank passbook.',
                'excerpt_gu' => 'આધાર, જાતિ પ્રમાણપત્ર, રેશન કાર્ડ, બેંક પાસબુક.',
                'body_en' => 'Bring what you have. The coordinator will say what is still missing.',
                'body_gu' => 'જે હોય તે લાવો. સંયોજક કહેશે કે શું બાકી છે.',
            ],
            [
                'slug' => 'faq-1',
                'title_en' => 'Do you guarantee a government sanction?',
                'title_gu' => 'સરકારી મંજૂરીની ખાતરી છે?',
                'excerpt_en' => 'No. We walk with the family until the next step is clear.',
                'excerpt_gu' => 'ના. આગળનું પગલું સ્પષ્ટ થાય ત્યાં સુધી પરિવાર સાથે ચાલીએ.',
                'body_en' => 'We write the file, keep a case number, and follow up with the desk. A sanction is the department\'s decision.',
                'body_gu' => 'અમે ફાઇલ લખીએ, કેસ નંબર રાખીએ, અને ડેસ્ક સાથે ફોલો-અપ લઈએ. મંજૂરી વિભાગની છે.',
            ],
            [
                'slug' => 'faq-2',
                'title_en' => 'How do I track my application without an app login?',
                'title_gu' => 'લોગિન વગર અરજી કેવી રીતે ટ્રેક કરું?',
                'excerpt_en' => 'Send an OTP to the email written on the application.',
                'excerpt_gu' => 'અરજી પર લખેલી ઇમેઇલ પર OTP મોકલો.',
                'body_en' => 'The code only unlocks that case. Guests cannot open another family file.',
                'body_gu' => 'કોડ ફક્ત એ કેસ ખોલે. મહેમાન બીજા પરિવારની ફાઇલ ન જોઈ શકે.',
            ],
            [
                'slug' => 'faq-3',
                'title_en' => 'When will a mentor or volunteer visit?',
                'title_gu' => 'મેન્ટર કે વોલેન્ટિયર ક્યારે આવશે?',
                'excerpt_en' => 'When the desk assigns the case and the helper accepts.',
                'excerpt_gu' => 'ડેસ્ક કેસ સોંપે અને સહાયક સ્વીકારે ત્યારે.',
                'body_en' => 'You will see the visit on Appointments after the helper schedules a follow-up.',
                'body_gu' => 'સહાયક ફોલો-અપ નક્કી કરે ત્યારે અપોઇન્ટમેન્ટમાં મુલાકાત દેખાશે.',
            ],
        ];

        foreach ($blocks as $i => $row) {
            $this->upsertItem('home', $row['slug'], [
                'title_en' => $row['title_en'],
                'title_gu' => $row['title_gu'],
                'excerpt_en' => $row['excerpt_en'],
                'excerpt_gu' => $row['excerpt_gu'],
                'body_en' => $row['body_en'],
                'body_gu' => $row['body_gu'],
                'is_published' => true,
                'sort_order' => $i,
            ]);
        }
    }
}
