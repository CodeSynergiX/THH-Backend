<?php

namespace Database\Seeders;

use App\Domains\Cases\Models\Application;
use App\Domains\Cases\Models\ApplicationAssignment;
use App\Domains\Cases\Models\ApplicationDocument;
use App\Domains\Cases\Models\ApplicationMessage;
use App\Domains\Cases\Models\ApplicationTimelineEvent;
use App\Domains\Cases\Models\FollowUp;
use App\Domains\Cases\Models\WorkflowTransition;
use App\Domains\Content\Models\BloodRequest;
use App\Domains\Content\Models\BusinessIdea;
use App\Domains\Content\Models\Category;
use App\Domains\Content\Models\HealthCamp;
use App\Domains\Content\Models\Hospital;
use App\Domains\Content\Models\JobPosting;
use App\Domains\Content\Models\Library;
use App\Domains\Content\Models\MentorProfile;
use App\Domains\Content\Models\MockTest;
use App\Domains\Content\Models\MockTestQuestion;
use App\Domains\Content\Models\SakhiCircle;
use App\Domains\Content\Models\Scheme;
use App\Domains\Content\Models\Scholarship;
use App\Domains\Content\Models\StudyMaterial;
use App\Domains\Content\Models\SubCategory;
use App\Domains\Content\Models\VillageReport;
use App\Domains\Settings\Models\AuditLog;
use App\Domains\Settings\Models\Banner;
use App\Domains\Settings\Models\Faq;
use App\Domains\Settings\Models\HomeTile;
use App\Domains\Settings\Models\Setting;
use App\Domains\Settings\Models\StaticPage;
use App\Domains\Users\Models\District;
use App\Domains\Users\Models\Taluka;
use App\Domains\Users\Models\Village;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles & Permissions Setup
        $roles = ['super_admin', 'admin', 'collector', 'staff', 'mentor', 'citizen', 'volunteer'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'sanctum']);
        }

        // 2. Geographic Hierarchy (5 Tribal Districts in Gujarat)
        $districtsData = [
            [
                'name_en' => 'Dang',
                'name_gu' => 'ડાંગ',
                'code' => 'DANG',
                'talukas' => [
                    [
                        'name_en' => 'Ahwa',
                        'name_gu' => 'આહવા',
                        'code' => 'AHWA',
                        'villages' => [
                            ['name_en' => 'Ahwa', 'name_gu' => 'આહવા', 'pincode' => '394710', 'lat' => 20.7583, 'lng' => 73.6872],
                            ['name_en' => 'Saputara', 'name_gu' => 'સાપુતારા', 'pincode' => '394720', 'lat' => 20.5796, 'lng' => 73.7478],
                            ['name_en' => 'Shamgahan', 'name_gu' => 'શામગહાન', 'pincode' => '394710', 'lat' => 20.8123, 'lng' => 73.7011],
                            ['name_en' => 'Gadhvi', 'name_gu' => 'ગઢવી', 'pincode' => '394710', 'lat' => 20.7301, 'lng' => 73.6420],
                        ],
                    ],
                    [
                        'name_en' => 'Waghai',
                        'name_gu' => 'વઘઈ',
                        'code' => 'WAGH',
                        'villages' => [
                            ['name_en' => 'Waghai', 'name_gu' => 'વઘઈ', 'pincode' => '394730', 'lat' => 20.7716, 'lng' => 73.5042],
                            ['name_en' => 'Ambapada', 'name_gu' => 'આંબાપાડા', 'pincode' => '394730', 'lat' => 20.7930, 'lng' => 73.5210],
                        ],
                    ],
                    [
                        'name_en' => 'Subir',
                        'name_gu' => 'સુબીર',
                        'code' => 'SUBI',
                        'villages' => [
                            ['name_en' => 'Subir', 'name_gu' => 'સુબીર', 'pincode' => '394716', 'lat' => 20.9122, 'lng' => 73.7431],
                            ['name_en' => 'Pipaldahad', 'name_gu' => 'પીપલદહડ', 'pincode' => '394716', 'lat' => 20.8845, 'lng' => 73.7820],
                        ],
                    ],
                ],
            ],
            [
                'name_en' => 'Dahod',
                'name_gu' => 'દાહોદ',
                'code' => 'DAHOD',
                'talukas' => [
                    [
                        'name_en' => 'Dahod',
                        'name_gu' => 'દાહોદ',
                        'code' => 'DAH',
                        'villages' => [
                            ['name_en' => 'Rozam', 'name_gu' => 'રોઝમ', 'pincode' => '389151', 'lat' => 22.8398, 'lng' => 74.2541],
                            ['name_en' => 'Chhapri', 'name_gu' => 'છાપરી', 'pincode' => '389151', 'lat' => 22.8450, 'lng' => 74.2610],
                            ['name_en' => 'Chandwana', 'name_gu' => 'ચાંદવાણા', 'pincode' => '389151', 'lat' => 22.8105, 'lng' => 74.2300],
                        ],
                    ],
                    [
                        'name_en' => 'Garbada',
                        'name_gu' => 'ગરબાડા',
                        'code' => 'GAR',
                        'villages' => [
                            ['name_en' => 'Gangardi', 'name_gu' => 'ગાંગરડી', 'pincode' => '389155', 'lat' => 22.7150, 'lng' => 74.3200],
                            ['name_en' => 'Nani Kharaj', 'name_gu' => 'નાની ખરાજ', 'pincode' => '389155', 'lat' => 22.7310, 'lng' => 74.3410],
                        ],
                    ],
                    [
                        'name_en' => 'Zalod',
                        'name_gu' => 'ઝાલોદ',
                        'code' => 'ZAL',
                        'villages' => [
                            ['name_en' => 'Zalod', 'name_gu' => 'ઝાલોદ', 'pincode' => '389170', 'lat' => 23.1360, 'lng' => 74.1540],
                            ['name_en' => 'Singvad', 'name_gu' => 'સિંગવડ', 'pincode' => '389170', 'lat' => 23.0850, 'lng' => 74.0980],
                        ],
                    ],
                    [
                        'name_en' => 'Limkheda',
                        'name_gu' => 'લીમખેડા',
                        'code' => 'LIM',
                        'villages' => [
                            ['name_en' => 'Limkheda', 'name_gu' => 'લીમખેડા', 'pincode' => '389180', 'lat' => 22.8330, 'lng' => 73.9900],
                        ],
                    ],
                ],
            ],
            [
                'name_en' => 'Narmada',
                'name_gu' => 'નર્મદા',
                'code' => 'NARMADA',
                'talukas' => [
                    [
                        'name_en' => 'Nandod',
                        'name_gu' => 'નાંદોદ',
                        'code' => 'NAN',
                        'villages' => [
                            ['name_en' => 'Rajpipla', 'name_gu' => 'રાજપીપળા', 'pincode' => '393145', 'lat' => 21.8711, 'lng' => 73.5029],
                            ['name_en' => 'Kevadia', 'name_gu' => 'કેવડિયા', 'pincode' => '393151', 'lat' => 21.8829, 'lng' => 73.7196],
                        ],
                    ],
                    [
                        'name_en' => 'Dediapada',
                        'name_gu' => 'ડેડિયાપાડા',
                        'code' => 'DED',
                        'villages' => [
                            ['name_en' => 'Dediapada', 'name_gu' => 'ડેડિયાપાડા', 'pincode' => '393040', 'lat' => 21.6370, 'lng' => 73.5930],
                            ['name_en' => 'Dumkhal', 'name_gu' => 'દુમખલ', 'pincode' => '393040', 'lat' => 21.5790, 'lng' => 73.7100],
                            ['name_en' => 'Piplod', 'name_gu' => 'પીપલોદ', 'pincode' => '393040', 'lat' => 21.6120, 'lng' => 73.6210],
                        ],
                    ],
                    [
                        'name_en' => 'Sagbara',
                        'name_gu' => 'સાગબારા',
                        'code' => 'SAG',
                        'villages' => [
                            ['name_en' => 'Sagbara', 'name_gu' => 'સાગબારા', 'pincode' => '393050', 'lat' => 21.5420, 'lng' => 73.7850],
                            ['name_en' => 'Selamba', 'name_gu' => 'સેલંબા', 'pincode' => '393050', 'lat' => 21.5310, 'lng' => 73.7990],
                        ],
                    ],
                ],
            ],
            [
                'name_en' => 'Chhota Udepur',
                'name_gu' => 'છોટાઉદેપુર',
                'code' => 'CHHOTA_UDEPUR',
                'talukas' => [
                    [
                        'name_en' => 'Chhota Udepur',
                        'name_gu' => 'છોટાઉદેપુર',
                        'code' => 'CHU',
                        'villages' => [
                            ['name_en' => 'Chhota Udepur', 'name_gu' => 'છોટાઉદેપુર', 'pincode' => '391165', 'lat' => 22.3040, 'lng' => 74.0120],
                            ['name_en' => 'Rangpur', 'name_gu' => 'રંગપુર', 'pincode' => '391165', 'lat' => 22.3320, 'lng' => 74.0540],
                        ],
                    ],
                    [
                        'name_en' => 'Kawant',
                        'name_gu' => 'કવાંટ',
                        'code' => 'KAW',
                        'villages' => [
                            ['name_en' => 'Kawant', 'name_gu' => 'કવાંટ', 'pincode' => '391170', 'lat' => 22.1580, 'lng' => 74.0520],
                            ['name_en' => 'Panvad', 'name_gu' => 'પાનવડ', 'pincode' => '391170', 'lat' => 22.1890, 'lng' => 74.1100],
                            ['name_en' => 'Hampheshwar', 'name_gu' => 'હાંફેશ્વર', 'pincode' => '391170', 'lat' => 22.0910, 'lng' => 74.0950],
                        ],
                    ],
                    [
                        'name_en' => 'Nasvadi',
                        'name_gu' => 'નસવાડી',
                        'code' => 'NAS',
                        'villages' => [
                            ['name_en' => 'Nasvadi', 'name_gu' => 'નસવાડી', 'pincode' => '391180', 'lat' => 22.0290, 'lng' => 73.7650],
                        ],
                    ],
                ],
            ],
            [
                'name_en' => 'Tapi',
                'name_gu' => 'તાપી',
                'code' => 'TAPI',
                'talukas' => [
                    [
                        'name_en' => 'Vyara',
                        'name_gu' => 'વ્યારા',
                        'code' => 'VYA',
                        'villages' => [
                            ['name_en' => 'Vyara', 'name_gu' => 'વ્યારા', 'pincode' => '394650', 'lat' => 21.1120, 'lng' => 73.3980],
                            ['name_en' => 'Maypur', 'name_gu' => 'માયપુર', 'pincode' => '394650', 'lat' => 21.1350, 'lng' => 73.4210],
                        ],
                    ],
                    [
                        'name_en' => 'Songadh',
                        'name_gu' => 'સોનગઢ',
                        'code' => 'SON',
                        'villages' => [
                            ['name_en' => 'Songadh', 'name_gu' => 'સોનગઢ', 'pincode' => '394670', 'lat' => 21.1680, 'lng' => 73.5650],
                            ['name_en' => 'Fort Songadh', 'name_gu' => 'કિલ્લો સોનગઢ', 'pincode' => '394670', 'lat' => 21.1730, 'lng' => 73.5700],
                            ['name_en' => 'Doswada', 'name_gu' => 'દોસવાડા', 'pincode' => '394670', 'lat' => 21.1920, 'lng' => 73.5320],
                            ['name_en' => 'Ukai', 'name_gu' => 'ઉકાઈ', 'pincode' => '394680', 'lat' => 21.2510, 'lng' => 73.5820],
                        ],
                    ],
                    [
                        'name_en' => 'Uchchhal',
                        'name_gu' => 'ઉચ્છલ',
                        'code' => 'UCH',
                        'villages' => [
                            ['name_en' => 'Uchchhal', 'name_gu' => 'ઉચ્છલ', 'pincode' => '394375', 'lat' => 21.1780, 'lng' => 73.8340],
                        ],
                    ],
                    [
                        'name_en' => 'Nizar',
                        'name_gu' => 'નિઝર',
                        'code' => 'NIZ',
                        'villages' => [
                            ['name_en' => 'Nizar', 'name_gu' => 'નિઝર', 'pincode' => '394380', 'lat' => 21.4920, 'lng' => 74.2150],
                            ['name_en' => 'Raniamba', 'name_gu' => 'રાણીઆંબા', 'pincode' => '394380', 'lat' => 21.5120, 'lng' => 74.2400],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($districtsData as $dData) {
            $district = District::updateOrCreate(
                ['code' => $dData['code']],
                [
                    'name_en' => $dData['name_en'],
                    'name_gu' => $dData['name_gu'],
                    'is_active' => true,
                ]
            );

            foreach ($dData['talukas'] as $tData) {
                $taluka = Taluka::updateOrCreate(
                    ['district_id' => $district->id, 'code' => $tData['code']],
                    [
                        'name_en' => $tData['name_en'],
                        'name_gu' => $tData['name_gu'],
                        'is_active' => true,
                    ]
                );

                foreach ($tData['villages'] as $vData) {
                    Village::updateOrCreate(
                        ['taluka_id' => $taluka->id, 'name_en' => $vData['name_en']],
                        [
                            'name_gu' => $vData['name_gu'],
                            'pincode' => $vData['pincode'],
                            'lat' => $vData['lat'],
                            'lng' => $vData['lng'],
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        // Fetch primary location records for references
        $dangDistrict = District::where('code', 'DANG')->first();
        $dahodDistrict = District::where('code', 'DAHOD')->first();
        $narmadaDistrict = District::where('code', 'NARMADA')->first();
        $chhotaDistrict = District::where('code', 'CHHOTA_UDEPUR')->first();
        $tapiDistrict = District::where('code', 'TAPI')->first();

        $ahwaTaluka = Taluka::where('code', 'AHWA')->first();
        $garbadaTaluka = Taluka::where('code', 'GAR')->first();
        $dediapadaTaluka = Taluka::where('code', 'DED')->first();
        $kawantTaluka = Taluka::where('code', 'KAW')->first();
        $songadhTaluka = Taluka::where('code', 'SON')->first();

        $ahwaVillage = Village::where('name_en', 'Ahwa')->first();
        $saputaraVillage = Village::where('name_en', 'Saputara')->first();
        $shamgahanVillage = Village::where('name_en', 'Shamgahan')->first();
        $rozamVillage = Village::where('name_en', 'Rozam')->first();
        $gangardiVillage = Village::where('name_en', 'Gangardi')->first();
        $dumkhalVillage = Village::where('name_en', 'Dumkhal')->first();
        $kawantVillage = Village::where('name_en', 'Kawant')->first();
        $songadhVillage = Village::where('name_en', 'Songadh')->first();

        // 3. 8 Core Welfare Categories & Subcategories
        $categoriesData = [
            [
                'slug' => 'schemes',
                'icon' => 'Award',
                'sort_order' => 1,
                'subcategories' => [
                    ['slug' => 'pm_kisan', 'icon' => 'Coins', 'sort_order' => 1],
                    ['slug' => 'ma_amrutam', 'icon' => 'ShieldAlert', 'sort_order' => 2],
                    ['slug' => 'tribal_housing', 'icon' => 'Home', 'sort_order' => 3],
                    ['slug' => 'solar_pump', 'icon' => 'Sun', 'sort_order' => 4],
                ],
            ],
            [
                'slug' => 'scholarships',
                'icon' => 'GraduationCap',
                'sort_order' => 2,
                'subcategories' => [
                    ['slug' => 'post_matric', 'icon' => 'BookOpen', 'sort_order' => 1],
                    ['slug' => 'vidyasadhana_bicycle', 'icon' => 'Bike', 'sort_order' => 2],
                    ['slug' => 'hostel_admission', 'icon' => 'Building', 'sort_order' => 3],
                    ['slug' => 'higher_education_grant', 'icon' => 'FileText', 'sort_order' => 4],
                ],
            ],
            [
                'slug' => 'health',
                'icon' => 'HeartPulse',
                'sort_order' => 3,
                'subcategories' => [
                    ['slug' => 'sickle_cell_aid', 'icon' => 'Activity', 'sort_order' => 1],
                    ['slug' => 'emergency_treatment', 'icon' => 'Ambulance', 'sort_order' => 2],
                    ['slug' => 'maternal_health', 'icon' => 'UserCheck', 'sort_order' => 3],
                    ['slug' => 'disability_aid', 'icon' => 'Accessibility', 'sort_order' => 4],
                ],
            ],
            [
                'slug' => 'legal_aid',
                'icon' => 'Scale',
                'sort_order' => 4,
                'subcategories' => [
                    ['slug' => 'land_dispute', 'icon' => 'FileSpreadsheet', 'sort_order' => 1],
                    ['slug' => 'fra_forest_rights', 'icon' => 'MapPin', 'sort_order' => 2],
                    ['slug' => 'caste_certificate', 'icon' => 'FileCheck', 'sort_order' => 3],
                    ['slug' => 'revenue_guidance', 'icon' => 'HelpCircle', 'sort_order' => 4],
                ],
            ],
            [
                'slug' => 'jobs_skills',
                'icon' => 'Briefcase',
                'sort_order' => 5,
                'subcategories' => [
                    ['slug' => 'iti_admission', 'icon' => 'Wrench', 'sort_order' => 1],
                    ['slug' => 'tribal_artisan_market', 'icon' => 'ShoppingBag', 'sort_order' => 2],
                    ['slug' => 'skill_development', 'icon' => 'Zap', 'sort_order' => 3],
                    ['slug' => 'apprentice_placement', 'icon' => 'UserPlus', 'sort_order' => 4],
                ],
            ],
            [
                'slug' => 'forest_rights',
                'icon' => 'Trees',
                'sort_order' => 6,
                'subcategories' => [
                    ['slug' => 'individual_rights', 'icon' => 'FileText', 'sort_order' => 1],
                    ['slug' => 'community_rights', 'icon' => 'Users', 'sort_order' => 2],
                    ['slug' => 'minor_forest_produce', 'icon' => 'Package', 'sort_order' => 3],
                    ['slug' => 'joint_forest_mgmt', 'icon' => 'Shield', 'sort_order' => 4],
                ],
            ],
            [
                'slug' => 'village_issues',
                'icon' => 'Building2',
                'sort_order' => 7,
                'subcategories' => [
                    ['slug' => 'drinking_water', 'icon' => 'Droplets', 'sort_order' => 1],
                    ['slug' => 'road_connectivity', 'icon' => 'Navigation', 'sort_order' => 2],
                    ['slug' => 'street_light', 'icon' => 'Lightbulb', 'sort_order' => 3],
                    ['slug' => 'drainage_sanitation', 'icon' => 'Trash2', 'sort_order' => 4],
                ],
            ],
            [
                'slug' => 'agriculture_infra',
                'icon' => 'Sprout',
                'sort_order' => 8,
                'subcategories' => [
                    ['slug' => 'drip_irrigation', 'icon' => 'CloudRain', 'sort_order' => 1],
                    ['slug' => 'seed_fertilizer', 'icon' => 'CheckCircle', 'sort_order' => 2],
                    ['slug' => 'borewell_subsidy', 'icon' => 'Layers', 'sort_order' => 3],
                    ['slug' => 'cattle_shed', 'icon' => 'Smile', 'sort_order' => 4],
                ],
            ],
        ];

        foreach ($categoriesData as $cData) {
            $cat = Category::updateOrCreate(
                ['slug' => $cData['slug']],
                [
                    'icon' => $cData['icon'],
                    'sort_order' => $cData['sort_order'],
                    'is_active' => true,
                ]
            );

            foreach ($cData['subcategories'] as $scData) {
                SubCategory::updateOrCreate(
                    ['category_id' => $cat->id, 'slug' => $scData['slug']],
                    [
                        'icon' => $scData['icon'],
                        'sort_order' => $scData['sort_order'],
                        'is_active' => true,
                    ]
                );
            }
        }

        // 4. Workflow Transitions Matrix
        $transitions = [
            ['received', 'verification', ['staff', 'admin', 'super_admin'], false],
            ['verification', 'categorised', ['staff', 'admin', 'super_admin'], false],
            ['verification', 'needMoreInfo', ['staff', 'admin', 'super_admin'], true],
            ['verification', 'rejected', ['admin', 'super_admin'], true],
            ['categorised', 'assigned', ['staff', 'admin', 'super_admin', 'collector'], false],
            ['assigned', 'assistance', ['staff', 'mentor', 'volunteer', 'admin'], false],
            ['assistance', 'followUp', ['staff', 'admin', 'super_admin'], false],
            ['assistance', 'onHold', ['staff', 'admin', 'super_admin'], true],
            ['onHold', 'assistance', ['staff', 'admin', 'super_admin'], false],
            ['followUp', 'resolved', ['staff', 'admin', 'super_admin'], true],
            ['needMoreInfo', 'verification', ['citizen', 'staff', 'admin'], false],
            ['resolved', 'reopened', ['admin', 'super_admin'], true],
            ['rejected', 'reopened', ['admin', 'super_admin'], true],
        ];

        foreach ($transitions as [$from, $to, $rolesAllowed, $requiresNote]) {
            WorkflowTransition::updateOrCreate(
                ['from_status' => $from, 'to_status' => $to],
                [
                    'allowed_roles' => $rolesAllowed,
                    'requires_note' => $requiresNote,
                    'requires_documents' => false,
                ]
            );
        }

        // 5. Standard Roles & Users
        $defaultPassword = Hash::make('Password@123');

        // Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@ggvt.org'],
            [
                'name' => 'Rameshbhai Patel (રમેશભાઈ પટેલ)',
                'phone' => '9876500001',
                'password' => $defaultPassword,
                'gender' => 'male',
                'age' => 52,
                'occupation' => 'Chief Executive Trustee, GGVT',
                'education' => 'M.S.W., LL.B.',
                'income_category' => 'above_poverty_line',
                'community' => 'Patel / Tribal Welfare Activist',
                'locale' => 'gu',
                'theme_preference' => 'system',
                'is_active' => true,
                'consent_at' => now(),
            ]
        );
        $superAdmin->syncRoles(['admin', 'super_admin']);

        // District Officer / Collector Dang
        $collectorDang = User::updateOrCreate(
            ['email' => 'collector.dang@ggvt.org'],
            [
                'name' => 'Priyankaben Gamit (પ્રિયંકાબેન ગામીત)',
                'phone' => '9876500002',
                'password' => $defaultPassword,
                'gender' => 'female',
                'age' => 41,
                'district_id' => $dangDistrict?->id,
                'taluka_id' => $ahwaTaluka?->id,
                'village_id' => $ahwaVillage?->id,
                'occupation' => 'District Liaison Officer (Dang)',
                'education' => 'M.A. Public Administration',
                'income_category' => 'middle',
                'community' => 'Gamit ST',
                'locale' => 'gu',
                'is_active' => true,
                'consent_at' => now(),
            ]
        );
        $collectorDang->syncRoles(['collector', 'staff']);

        // Field Staff 1: Ahwa Dang
        $sevakAhwa = User::updateOrCreate(
            ['email' => 'sevak.ahwa@ggvt.org'],
            [
                'name' => 'Bipinbhai Bhoya (બિપિનભાઈ ભોયા)',
                'phone' => '9876500003',
                'password' => $defaultPassword,
                'gender' => 'male',
                'age' => 34,
                'district_id' => $dangDistrict?->id,
                'taluka_id' => $ahwaTaluka?->id,
                'village_id' => $ahwaVillage?->id,
                'occupation' => 'Gram Sevak / Field Coordinator',
                'education' => 'B.S.W.',
                'income_category' => 'middle',
                'community' => 'Bhoya ST',
                'locale' => 'gu',
                'is_active' => true,
                'consent_at' => now(),
            ]
        );
        $sevakAhwa->syncRoles(['staff']);

        // Field Staff 2: Dahod
        $sevakDahod = User::updateOrCreate(
            ['email' => 'sevak.dahod@ggvt.org'],
            [
                'name' => 'Kailasben Vasava (કૈલાસબેન વસાવા)',
                'phone' => '9876500004',
                'password' => $defaultPassword,
                'gender' => 'female',
                'age' => 29,
                'district_id' => $dahodDistrict?->id,
                'taluka_id' => $garbadaTaluka?->id,
                'village_id' => $gangardiVillage?->id,
                'occupation' => 'Community Mobilizer',
                'education' => 'B.A. Sociology',
                'income_category' => 'middle',
                'community' => 'Vasava ST',
                'locale' => 'gu',
                'is_active' => true,
                'consent_at' => now(),
            ]
        );
        $sevakDahod->syncRoles(['staff']);

        // Field Staff 3: Narmada
        $sevakNarmada = User::updateOrCreate(
            ['email' => 'sevak.narmada@ggvt.org'],
            [
                'name' => 'Maheshbhai Tadvi (મહેશભાઈ તડવી)',
                'phone' => '9876500005',
                'password' => $defaultPassword,
                'gender' => 'male',
                'age' => 38,
                'district_id' => $narmadaDistrict?->id,
                'taluka_id' => $dediapadaTaluka?->id,
                'village_id' => $dumkhalVillage?->id,
                'occupation' => 'Field Executive',
                'education' => 'Diploma Agriculture',
                'income_category' => 'middle',
                'community' => 'Tadvi ST',
                'locale' => 'gu',
                'is_active' => true,
                'consent_at' => now(),
            ]
        );
        $sevakNarmada->syncRoles(['staff']);

        // Mentors
        $mentorEducation = User::updateOrCreate(
            ['email' => 'mentor.education@ggvt.org'],
            [
                'name' => 'Dr. Arvindbhai Rathwa (ડો. અરવિંદભાઈ રાઠવા)',
                'phone' => '9876500006',
                'password' => $defaultPassword,
                'gender' => 'male',
                'age' => 48,
                'district_id' => $chhotaDistrict?->id,
                'taluka_id' => $kawantTaluka?->id,
                'village_id' => $kawantVillage?->id,
                'occupation' => 'Professor of Tribal Studies & Career Counselor',
                'education' => 'Ph.D. Education',
                'income_category' => 'high',
                'community' => 'Rathwa ST',
                'locale' => 'gu',
                'is_active' => true,
                'consent_at' => now(),
            ]
        );
        $mentorEducation->syncRoles(['mentor']);

        MentorProfile::updateOrCreate(
            ['user_id' => $mentorEducation->id],
            [
                'expertise' => ['Higher Education', 'Scholarships', 'GPSC Coaching', 'Hostel Guidance'],
                'bio' => '20+ years guiding tribal youth in Gujarat towards university education and competitive exam success.',
                'designation' => 'Associate Professor',
                'organization' => 'Gujarat Vidyapith / Tribal Research Institute',
                'is_available' => true,
                'rating' => 4.95,
            ]
        );

        $mentorLegal = User::updateOrCreate(
            ['email' => 'mentor.legal@ggvt.org'],
            [
                'name' => 'Adv. Jyotsnaben Chaudhary (એડવોકેટ જ્યોત્સ્નાબેન ચૌધરી)',
                'phone' => '9876500007',
                'password' => $defaultPassword,
                'gender' => 'female',
                'age' => 43,
                'district_id' => $tapiDistrict?->id,
                'taluka_id' => $songadhTaluka?->id,
                'village_id' => $songadhVillage?->id,
                'occupation' => 'High Court Advocate & FRA Legal Advisor',
                'education' => 'LL.M. Constitutional Law',
                'income_category' => 'high',
                'community' => 'Chaudhary ST',
                'locale' => 'gu',
                'is_active' => true,
                'consent_at' => now(),
            ]
        );
        $mentorLegal->syncRoles(['mentor']);

        MentorProfile::updateOrCreate(
            ['user_id' => $mentorLegal->id],
            [
                'expertise' => ['Forest Rights Act (FRA)', 'Land Title Regularization', 'Tribal Land Protections', 'Caste Certificate Verification'],
                'bio' => 'Practicing advocate dedicated to tribal land rights and village community forest rights resolution.',
                'designation' => 'Legal Advisor',
                'organization' => 'GGVT Legal Cell',
                'is_available' => true,
                'rating' => 5.00,
            ]
        );

        $mentorAgri = User::updateOrCreate(
            ['email' => 'mentor.agri@ggvt.org'],
            [
                'name' => 'Kantibhai Dindor (કાંતિભાઈ ડિંડોર)',
                'phone' => '9876500008',
                'password' => $defaultPassword,
                'gender' => 'male',
                'age' => 50,
                'district_id' => $dahodDistrict?->id,
                'taluka_id' => $garbadaTaluka?->id,
                'village_id' => $gangardiVillage?->id,
                'occupation' => 'Organic Farming Specialist & Soil Scientist',
                'education' => 'M.Sc. Agriculture',
                'income_category' => 'middle',
                'community' => 'Bhil / Dindor ST',
                'locale' => 'gu',
                'is_active' => true,
                'consent_at' => now(),
            ]
        );
        $mentorAgri->syncRoles(['mentor']);

        MentorProfile::updateOrCreate(
            ['user_id' => $mentorAgri->id],
            [
                'expertise' => ['Organic Farming', 'Drip Irrigation', 'Mushroom Cultivation', 'Minor Forest Produce Processing'],
                'bio' => 'Pioneer in tribal watershed development and sustainable agro-forestry in eastern Gujarat.',
                'designation' => 'Senior Agri Consultant',
                'organization' => 'Krishi Vigyan Kendra (KVK)',
                'is_available' => true,
                'rating' => 4.88,
            ]
        );

        // Tribal Citizens (Realistic ground applicants)
        $citizensData = [
            [
                'name' => 'Sureshbhai Laxmanbhai Gamit (સુરેશભાઈ લક્ષ્મણભાઈ ગામીત)',
                'phone' => '9825000011',
                'gender' => 'male',
                'age' => 23,
                'district_id' => $dangDistrict?->id,
                'taluka_id' => $ahwaTaluka?->id,
                'village_id' => $shamgahanVillage?->id,
                'occupation' => 'Student / College Youth',
                'education' => 'B.A. Final Year',
                'income_category' => 'below_poverty_line',
                'community' => 'Gamit ST',
            ],
            [
                'name' => 'Manishaben Dilipbhai Rathwa (મનીષાબેન દિલીપભાઈ રાઠવા)',
                'phone' => '9825000012',
                'gender' => 'female',
                'age' => 31,
                'district_id' => $chhotaDistrict?->id,
                'taluka_id' => $kawantTaluka?->id,
                'village_id' => $kawantVillage?->id,
                'occupation' => 'Traditional Pithora Artisan',
                'education' => 'Class 10th',
                'income_category' => 'below_poverty_line',
                'community' => 'Rathwa ST',
            ],
            [
                'name' => 'Dineshbhai Somabhai Vasava (દિનેશભાઈ સોમાભાઈ વસાવા)',
                'phone' => '9825000013',
                'gender' => 'male',
                'age' => 42,
                'district_id' => $narmadaDistrict?->id,
                'taluka_id' => $dediapadaTaluka?->id,
                'village_id' => $dumkhalVillage?->id,
                'occupation' => 'Small Farmer',
                'education' => 'Class 8th',
                'income_category' => 'antodaya',
                'community' => 'Vasava ST',
            ],
            [
                'name' => 'Kavitaben Bharatbhai Bhil (કવિતાબેન ભરતભાઈ ભીલ)',
                'phone' => '9825000014',
                'gender' => 'female',
                'age' => 26,
                'district_id' => $dahodDistrict?->id,
                'taluka_id' => $garbadaTaluka?->id,
                'village_id' => $gangardiVillage?->id,
                'occupation' => 'Mother / Anganwadi Helper',
                'education' => 'Class 12th',
                'income_category' => 'below_poverty_line',
                'community' => 'Bhil ST',
            ],
            [
                'name' => 'Raju Mohanbhai Padvi (રાજુ મોહનભાઈ પાડવી)',
                'phone' => '9825000015',
                'gender' => 'male',
                'age' => 28,
                'district_id' => $tapiDistrict?->id,
                'taluka_id' => $songadhTaluka?->id,
                'village_id' => $songadhVillage?->id,
                'occupation' => 'ITI Electrician',
                'education' => 'ITI Wireman',
                'income_category' => 'lower_middle',
                'community' => 'Padvi ST',
            ],
            [
                'name' => 'Shantaben Virjibhai Kotwal (શાંતાબેન વીરજીભાઈ કોટવાળ)',
                'phone' => '9825000016',
                'gender' => 'female',
                'age' => 54,
                'district_id' => $dangDistrict?->id,
                'taluka_id' => $ahwaTaluka?->id,
                'village_id' => $saputaraVillage?->id,
                'occupation' => 'Bamboo Craft Worker',
                'education' => 'Non-formal',
                'income_category' => 'below_poverty_line',
                'community' => 'Kotwalia ST (PVTG)',
            ],
            [
                'name' => 'Harishbhai Jalamjibhai Baria (હરીશભાઈ જાલમજીભાઈ બારિયા)',
                'phone' => '9825000017',
                'gender' => 'male',
                'age' => 36,
                'district_id' => $dahodDistrict?->id,
                'taluka_id' => $garbadaTaluka?->id,
                'village_id' => $rozamVillage?->id,
                'occupation' => 'Farmer & Milk Producer',
                'education' => 'Class 10th',
                'income_category' => 'below_poverty_line',
                'community' => 'Baria ST',
            ],
        ];

        $citizens = [];
        foreach ($citizensData as $cData) {
            $citizen = User::updateOrCreate(
                ['phone' => $cData['phone']],
                [
                    'name' => $cData['name'],
                    'gender' => $cData['gender'],
                    'age' => $cData['age'],
                    'district_id' => $cData['district_id'],
                    'taluka_id' => $cData['taluka_id'],
                    'village_id' => $cData['village_id'],
                    'occupation' => $cData['occupation'],
                    'education' => $cData['education'],
                    'income_category' => $cData['income_category'],
                    'community' => $cData['community'],
                    'locale' => 'gu',
                    'is_active' => true,
                    'consent_at' => now(),
                ]
            );
            $citizen->syncRoles(['citizen']);
            $citizens[] = $citizen;
        }

        // Cache category objects for case mapping
        $catSchemes = Category::where('slug', 'schemes')->first();
        $catScholarships = Category::where('slug', 'scholarships')->first();
        $catHealth = Category::where('slug', 'health')->first();
        $catLegal = Category::where('slug', 'legal_aid')->first();
        $catJobs = Category::where('slug', 'jobs_skills')->first();
        $catForest = Category::where('slug', 'forest_rights')->first();
        $catVillage = Category::where('slug', 'village_issues')->first();
        $catAgri = Category::where('slug', 'agriculture_infra')->first();

        $subPostMatric = SubCategory::where('slug', 'post_matric')->first();
        $subSolarPump = SubCategory::where('slug', 'solar_pump')->first();
        $subSickleCell = SubCategory::where('slug', 'sickle_cell_aid')->first();
        $subFra = SubCategory::where('slug', 'fra_forest_rights')->first();
        $subWater = SubCategory::where('slug', 'drinking_water')->first();
        $subArtisan = SubCategory::where('slug', 'tribal_artisan_market')->first();
        $subCycle = SubCategory::where('slug', 'vidyasadhana_bicycle')->first();
        $subRoad = SubCategory::where('slug', 'road_connectivity')->first();
        $subEmergMed = SubCategory::where('slug', 'emergency_treatment')->first();
        $subCasteCert = SubCategory::where('slug', 'caste_certificate')->first();
        $subPmKisan = SubCategory::where('slug', 'pm_kisan')->first();
        $subStreetLight = SubCategory::where('slug', 'street_light')->first();
        $subHigherGrant = SubCategory::where('slug', 'higher_education_grant')->first();
        $subMaternal = SubCategory::where('slug', 'maternal_health')->first();
        $subDrip = SubCategory::where('slug', 'drip_irrigation')->first();
        $subCommunityFra = SubCategory::where('slug', 'community_rights')->first();

        // 6. 16 Realistic Applications across ALL lifecycle states
        // Format: THH-2026-00001 through THH-2026-00016
        $applicationsData = [
            [
                'case_no' => 'THH-2026-00001',
                'user' => $citizens[0], // Sureshbhai Gamit
                'category_id' => $catScholarships?->id,
                'sub_category_id' => $subPostMatric?->id,
                'title' => 'કોલેજ પોસ્ટ મેટ્રિક શિષ્યવૃત્તિ બેંક ખાતામાં જમા ન થવા બાબત (Post-Matric ST Scholarship Delay)',
                'description' => 'છેલ્લા ૮ મહિનાથી ત્રીજા વર્ષની પોસ્ટ મેટ્રિક સ્કોલરશિપ પોર્ટલ પર મંજૂર બતાવે છે પરંતુ DBT ખાતામાં જમા થયેલ નથી. આર્થિક પરિસ્થિતિ નબળી હોવાથી ફી ભરવામાં મુશ્કેલી પડે છે.',
                'urgency' => 'urgent',
                'priority' => 'high',
                'status' => Application::STATUS_RESOLVED,
                'village_id' => $shamgahanVillage?->id,
                'lat' => 20.8123,
                'lng' => 73.7011,
                'assignee' => $sevakAhwa,
                'created_at' => Carbon::now()->subDays(25),
                'resolved_at' => Carbon::now()->subDays(2),
                'rating' => 5,
                'feedback' => 'ગ્રામ સેવક બિપિનભાઈએ તાત્કાલિક તાલુકા કચેરીએ જઈને NPCI મેપિંગ સુધરાવી આપ્યું. ખૂબ ખૂબ આભાર!',
                'milestones' => [
                    ['received', 'અરજી સફળતાપૂર્વક નોંધાઈ', 'Citizen submitted scholarship delay request with fee receipt.'],
                    ['verification', 'દસ્તાવેજોની ચકાસણી પૂર્ણ', 'Verified bank passbook, Aadhaar seeding and college bonafide certificate.'],
                    ['categorised', 'શિક્ષણ અને શિષ્યવૃત્તિ વિભાગમાં મોકલેલ', 'Classified under Post-Matric ST Scholarship DBT issues.'],
                    ['assigned', 'આહવા તાલુકા ગ્રામ સેવકને સોંપાયેલ', 'Assigned to Bipinbhai Bhoya for physical liaison at tribal development office.'],
                    ['assistance', 'કચેરી ખાતે NPCI સીડિંગ સુધારેલ', 'Field staff visited Ahwa Taluka Seva Sadan with bank manager to fix NPCI mapper.'],
                    ['followUp', 'ડીબીટી જમા ચકાસણી', 'Confirmed with citizen bank branch that Rs 14,200 scholarship successfully credited.'],
                    ['resolved', 'સમસ્યાનું સંપૂર્ણ નિરાકરણ', 'Case successfully resolved and citizen expressed high satisfaction.'],
                ],
                'documents' => [
                    ['Aadhaar Card Copy', 'documents/demo/aadhaar_00001.pdf', 'verified', $sevakAhwa->id],
                    ['College Bonafide Certificate', 'documents/demo/bonafide_00001.pdf', 'verified', $sevakAhwa->id],
                    ['Bank Passbook Copy with NPCI issue', 'documents/demo/passbook_00001.pdf', 'verified', $sevakAhwa->id],
                ],
                'follow_ups' => [
                    ['done', Carbon::now()->subDays(3), 'Follow-up call with student to confirm SMS receipt of bank credit.'],
                ],
            ],
            [
                'case_no' => 'THH-2026-00002',
                'user' => $citizens[2], // Dineshbhai Vasava
                'category_id' => $catAgri?->id,
                'sub_category_id' => $subSolarPump?->id,
                'title' => 'ખેતરમાં સૂર્ય ઊર્જા પંપ સ્થાપવા સબસિડી સહાય (Solar Irrigation Pump Scheme)',
                'description' => 'નર્મદા જિલ્લાના દુમખલ ગામે વીજળીનું લાઇન કનેક્શન ન હોવાથી ખેતીમાં સિંચાઈ શક્ય બનતી નથી. આદિવાસી કલ્યાણ વિભાગની સોલાર પંપ યોજના હેઠળ અરજી કરેલ છે, આગળની કાર્યવાહી માટે માર્ગદર્શન જોઈએ.',
                'urgency' => 'normal',
                'priority' => 'medium',
                'status' => Application::STATUS_ASSISTANCE,
                'village_id' => $dumkhalVillage?->id,
                'lat' => 21.5790,
                'lng' => 73.7100,
                'assignee' => $sevakNarmada,
                'created_at' => Carbon::now()->subDays(12),
                'resolved_at' => null,
                'milestones' => [
                    ['received', 'અરજી પ્રાપ્ત થઈ', 'Help request registered for Solar Pump setup.'],
                    ['verification', '7/12 અને 8-અ જમીન નકલ ચકાસી', 'Land records and tribal certificate verified.'],
                    ['categorised', 'કૃષિ અને સિંચાઈ સહાય', 'Categorised under PM KUSUM / GGRC tribal quota.'],
                    ['assigned', 'ડેડિયાપાડા સેવકને સોંપાયેલ', 'Assigned to Maheshbhai Tadvi for GGRC coordination.'],
                    ['assistance', 'સ્થળ પર સર્વે ટીમ મુલાકાત', 'GGRC site inspection scheduled and water bore test passed.'],
                ],
                'documents' => [
                    ['7-12 Land Title Copy', 'documents/demo/land_712_00002.pdf', 'verified', $sevakNarmada->id],
                    ['Borewell Water Report', 'documents/demo/water_test_00002.pdf', 'pending', null],
                ],
                'follow_ups' => [
                    ['pending', Carbon::now()->addDays(4), 'Confirm solar panel delivery date with vendor.'],
                ],
            ],
            [
                'case_no' => 'THH-2026-00003',
                'user' => $citizens[3], // Kavitaben Bhil
                'category_id' => $catHealth?->id,
                'sub_category_id' => $subSickleCell?->id,
                'title' => 'સિકલ સેલ એનિમિયા દર્દી માટે વિશેષ પોષણ સહાય અને દવા (Sickle Cell Anemia Support)',
                'description' => 'મારા ૮ વર્ષના બાળકને સિકલ સેલ ડીસીઝ (HbSS) હોવાનું નિદાન થયું છે. નિયમિત ફોલિક એસિડ ગોળીઓ અને હાઇડ્રોક્સીયુરિયા દવા દાહોદ સિવિલ હોસ્પિટલમાંથી મળવામાં મુશ્કેલી પડે છે.',
                'urgency' => 'urgent',
                'priority' => 'critical',
                'status' => Application::STATUS_FOLLOW_UP,
                'village_id' => $gangardiVillage?->id,
                'lat' => 22.7150,
                'lng' => 74.3200,
                'assignee' => $sevakDahod,
                'created_at' => Carbon::now()->subDays(18),
                'resolved_at' => null,
                'milestones' => [
                    ['received', 'અરજી પ્રાપ્ત થઈ', 'Urgent medical aid request for sickle cell child.'],
                    ['verification', 'સિકલ સેલ કાર્ડ ચકાસાયું', 'Confirmed HbSS status from Dahod Civil screening laboratory.'],
                    ['categorised', 'આરોગ્ય અને તબીબી સહાય', 'Categorised under Tribal Sickle Cell Mission.'],
                    ['assigned', 'ગરબાડા ક્ષેત્ર સેવકને સોંપાયેલ', 'Assigned to Kailasben Vasava.'],
                    ['assistance', 'દવાઓ ઉપલબ્ધ કરાવી', 'Handed over 3 months medication and registered with State Nutrition scheme.'],
                    ['followUp', 'બાળકના સ્વાસ્થ્ય તપાસણી ફોલોઅપ', 'Scheduled monthly checkup with CHC Garbada pediatrician.'],
                ],
                'documents' => [
                    ['Sickle Cell Screening Card', 'documents/demo/sickle_card_00003.pdf', 'verified', $sevakDahod->id],
                    ['Doctor Prescription Slip', 'documents/demo/prescription_00003.pdf', 'verified', $sevakDahod->id],
                ],
                'follow_ups' => [
                    ['pending', Carbon::now()->addDays(2), 'Check blood count report and nutrition kit delivery.'],
                ],
            ],
            [
                'case_no' => 'THH-2026-00004',
                'user' => $citizens[5], // Shantaben Kotwal
                'category_id' => $catForest?->id,
                'sub_category_id' => $subFra?->id,
                'title' => 'વન અધિકાર અધિનિયમ (FRA) હેઠળ વ્યક્તિગત જમીન દાવો દાખલ કરવા માર્ગદર્શન',
                'description' => 'અમે ત્રણ પેઢીથી સાપુતારા નજીકના જંગલમાં રહીએ છીએ અને વાંસકામ કરીએ છીએ. વન અધિકાર પત્રક ફોર્મ-અ ભરવા માટે કાગળો તૈયાર કરવામાં વકીલ સહાયની જરૂર છે.',
                'urgency' => 'normal',
                'priority' => 'high',
                'status' => Application::STATUS_VERIFICATION,
                'village_id' => $saputaraVillage?->id,
                'lat' => 20.5796,
                'lng' => 73.7478,
                'assignee' => $sevakAhwa,
                'created_at' => Carbon::now()->subDays(4),
                'resolved_at' => null,
                'milestones' => [
                    ['received', 'અરજી પ્રાપ્ત થઈ', 'FRA individual land claim assistance requested by PVTG family.'],
                    ['verification', 'ગ્રામ સભા દસ્તાવેજ ચકાસણી ચાલુ', 'Reviewing traditional occupation evidence and forest department notices.'],
                ],
                'documents' => [
                    ['Gram Sabha Resolution Draft', 'documents/demo/gram_sabha_00004.pdf', 'pending', null],
                ],
                'follow_ups' => [
                    ['pending', Carbon::now()->addDays(5), 'Meet village FRC committee president in Saputara.'],
                ],
            ],
            [
                'case_no' => 'THH-2026-00005',
                'user' => $citizens[0], // Sureshbhai Gamit
                'category_id' => $catVillage?->id,
                'sub_category_id' => $subWater?->id,
                'title' => 'શામગહાન ગામના પટેલ ફળિયામાં પીવાના પાણીના બોરવેલની મોટર બળી ગઈ છે',
                'description' => 'છેલ્લા ૧૫ દિવસથી ૩૫ આદિવાસી પરિવારોને પીવાના પાણી માટે ૧.૫ કિલોમીટર દૂર ઝરણા સુધી જવું પડે છે. પંચાયતમાં અરજી આપેલ છે પરંતુ કોઈ નિરાકરણ આવેલ નથી.',
                'urgency' => 'critical',
                'priority' => 'critical',
                'status' => Application::STATUS_ASSIGNED,
                'village_id' => $shamgahanVillage?->id,
                'lat' => 20.8123,
                'lng' => 73.7011,
                'assignee' => $collectorDang,
                'created_at' => Carbon::now()->subDays(7),
                'resolved_at' => null,
                'milestones' => [
                    ['received', 'અરજી પ્રાપ્ત થઈ', 'Critical drinking water issue reported in Shamgahan village.'],
                    ['verification', 'ટેલિફોનિક ચકાસણી પૂર્ણ', 'Spoke with village Sarpanch and confirmed 35 families affected.'],
                    ['categorised', 'ગામડાની પાયાની સુવિધાઓ', 'Categorised under WASMO / Water Supply infrastructure.'],
                    ['assigned', 'જિલ્લા લાયઝન અધિકારીને એસ્કેલેટ કરેલ', 'Assigned directly to Priyankaben Gamit (Collector Office Liaison).'],
                ],
                'documents' => [
                    ['Panchayat Complaint Letter Copy', 'documents/demo/panchayat_letter_00005.pdf', 'verified', $collectorDang->id],
                    ['Photo of Broken Handpump & Motor', 'documents/demo/broken_pump_00005.jpg', 'verified', $collectorDang->id],
                ],
                'follow_ups' => [
                    ['pending', Carbon::now()->addDays(1), 'Follow up with WASMO engineer for emergency water tanker and motor rewinding.'],
                ],
            ],
            [
                'case_no' => 'THH-2026-00006',
                'user' => $citizens[1], // Manishaben Rathwa
                'category_id' => $catJobs?->id,
                'sub_category_id' => $subArtisan?->id,
                'title' => 'પરંપરાગત પીઠોરા ચિત્રકળા અને હસ્તકળા ઉત્પાદનોના વેચાણ માટે માર્કેટ લિન્કેજ',
                'description' => 'કવાંટ વિસ્તારની ૧૨ આદિવાસી મહિલાઓ સુંદર પીઠોરા કેનવાસ અને માટીની કલાકૃતિઓ બનાવે છે. શહેરી મેળાઓ અથવા ટ્રાયફેડ (TRIFED) માં વેચાણ સ્ટોલ મેળવવા માર્ગદર્શન જોઈએ.',
                'urgency' => 'low',
                'priority' => 'medium',
                'status' => Application::STATUS_RECEIVED,
                'village_id' => $kawantVillage?->id,
                'lat' => 22.1580,
                'lng' => 74.0520,
                'assignee' => null,
                'created_at' => Carbon::now()->subHours(8),
                'resolved_at' => null,
                'milestones' => [
                    ['received', 'નવી અરજી સફળતાપૂર્વક નોંધાઈ', 'Help request registered online through mobile app.'],
                ],
                'documents' => [
                    ['Artisan Samples Portfolio', 'documents/demo/pithora_craft_00006.pdf', 'pending', null],
                ],
                'follow_ups' => [],
            ],
            [
                'case_no' => 'THH-2026-00007',
                'user' => $citizens[3], // Kavitaben Bhil
                'category_id' => $catScholarships?->id,
                'sub_category_id' => $subCycle?->id,
                'title' => 'વિદ્યાસાધના સાયકલ સહાય યોજના ફોર્મ પૂર્તતા (Vidyasadhana Bicycle Scheme)',
                'description' => 'ધોરણ ૯ માં ભણતી દીકરી માટે સાયકલ સહાયની અરજી કરી છે. શાળા ૫ કિલોમીટર દૂર હોવાથી નિયમિત જવા માટે સાયકલ અત્યંત જરૂરી છે.',
                'urgency' => 'normal',
                'priority' => 'medium',
                'status' => Application::STATUS_NEED_MORE_INFO,
                'village_id' => $gangardiVillage?->id,
                'lat' => 22.7150,
                'lng' => 74.3200,
                'assignee' => $sevakDahod,
                'created_at' => Carbon::now()->subDays(9),
                'resolved_at' => null,
                'milestones' => [
                    ['received', 'અરજી પ્રાપ્ત થઈ', 'Application received for bicycle subsidy.'],
                    ['verification', 'દસ્તાવેજ ચકાસણી', 'Verified student enrollment letter.'],
                    ['needMoreInfo', 'રેશન કાર્ડનું બીજું પાનું ખૂટે છે', 'Ration card family member page was blurry. Requested re-upload from citizen.'],
                ],
                'documents' => [
                    ['School Study Certificate', 'documents/demo/school_cert_00007.pdf', 'verified', $sevakDahod->id],
                    ['Ration Card Page 1', 'documents/demo/ration_00007.pdf', 'pending', null],
                ],
                'follow_ups' => [
                    ['pending', Carbon::now()->addDays(3), 'Call citizen to explain how to photograph second page of ration card.'],
                ],
            ],
            [
                'case_no' => 'THH-2026-00008',
                'user' => $citizens[2], // Dineshbhai Vasava
                'category_id' => $catVillage?->id,
                'sub_category_id' => $subRoad?->id,
                'title' => 'ચોમાસામાં કોઝવે ધોવાઈ જવાથી ગામનો સંપર્ક તૂટી જવાની રજૂઆત (Road Connectivity)',
                'description' => 'ડેડિયાપાડા તાલુકાના દુમખલથી પીપલોદ તરફ જતો કોઝવે ભારે વરસાદમાં ધોવાઈ ગયો છે. ૧૦૮ એમ્બ્યુલન્સ પણ ગામમાં પ્રવેશી શકતી નથી. પાકો બ્રિજ મંજૂર કરવા વિનંતી.',
                'urgency' => 'urgent',
                'priority' => 'high',
                'status' => Application::STATUS_ON_HOLD,
                'village_id' => $dumkhalVillage?->id,
                'lat' => 21.5790,
                'lng' => 73.7100,
                'assignee' => $sevakNarmada,
                'created_at' => Carbon::now()->subDays(30),
                'resolved_at' => null,
                'milestones' => [
                    ['received', 'અરજી પ્રાપ્ત થઈ', 'Bridge washing out reported by village sarpanch.'],
                    ['verification', 'સ્થળ નિરીક્ષણ પૂર્ણ', 'Field visit conducted by Maheshbhai Tadvi.'],
                    ['categorised', 'માર્ગ અને મકાન વિભાગ સંબંધિત', 'Sent proposal to R&B and PWD division Rajpipla.'],
                    ['assigned', 'સેવક મહેશભાઈને સોંપાયેલ', 'Assigned for inter-departmental correspondence.'],
                    ['assistance', 'પીડબલ્યુડી કાર્યપાલક ઇજનેરને રજૂઆત', 'Official representation submitted to District Planning Officer.'],
                    ['onHold', 'ચોમાસા બાદ પીડબલ્યુડી ટેન્ડરિંગ સુધી સ્થગિત', 'Work proposal approved in principle; awaiting post-monsoon budget release.'],
                ],
                'documents' => [
                    ['Site Damage Photographs', 'documents/demo/bridge_damage_00008.jpg', 'verified', $sevakNarmada->id],
                    ['R&B Acknowledgment Receipt', 'documents/demo/rnb_receipt_00008.pdf', 'verified', $sevakNarmada->id],
                ],
                'follow_ups' => [
                    ['pending', Carbon::now()->addDays(14), 'Follow up on district grant allocation for bridge reconstruction.'],
                ],
            ],
            [
                'case_no' => 'THH-2026-00009',
                'user' => $citizens[4], // Raju Padvi
                'category_id' => $catHealth?->id,
                'sub_category_id' => $subEmergMed?->id,
                'title' => 'વ્યારા સિવિલ હોસ્પિટલ ખાતે તાત્કાલિક O-નેગેટિવ રક્ત સહાય (Emergency Blood Request)',
                'description' => 'સોનગઢ તાલુકાના યુવકને અકસ્માતમાં ગંભીર ઈજા થતાં વ્યારા જનરલ હોસ્પિટલમાં O-Negative રક્તના ૨ યુનિટની તાત્કાલિક જરૂર હતી.',
                'urgency' => 'critical',
                'priority' => 'critical',
                'status' => Application::STATUS_RESOLVED,
                'village_id' => $songadhVillage?->id,
                'lat' => 21.1680,
                'lng' => 73.5650,
                'assignee' => $collectorDang,
                'created_at' => Carbon::now()->subDays(10),
                'resolved_at' => Carbon::now()->subDays(9),
                'rating' => 5,
                'feedback' => '૨ કલાકમાં રક્તદાતા શોધીને હોસ્પિટલે પહોંચાડ્યા અને દર્દીનો જીવ બચી ગયો. GGVT ટીમનો ખૂબ આભાર.',
                'milestones' => [
                    ['received', 'ઇમરજન્સી રક્ત વિનંતી પ્રાપ્ત થઈ', 'Emergency blood request registered at midnight.'],
                    ['verification', 'હોસ્પિટલ બ્લડ બેંક સાથે સંપર્ક', 'Verified blood group O-Negative requirement at Vyara.'],
                    ['categorised', 'તબીબી કટોકટી', 'High priority emergency protocol activated.'],
                    ['assigned', 'સ્થાનિક સ્વયંસેવક નેટવર્કને એલર્ટ', 'Alerted 15 registered tribal youth blood donors in Vyara/Songadh.'],
                    ['assistance', '૨ યુનિટ રક્તદાન સંપન્ન', 'Two youth donors reached hospital and donated within 2 hours.'],
                    ['resolved', 'દર્દી સુરક્ષિત - સમસ્યા ઉકેલાઈ', 'Patient surgery successful and out of danger.'],
                ],
                'documents' => [
                    ['Hospital Blood Requisition Slip', 'documents/demo/blood_slip_00009.pdf', 'verified', $collectorDang->id],
                ],
                'follow_ups' => [
                    ['done', Carbon::now()->subDays(8), 'Patient discharge status verified.'],
                ],
            ],
            [
                'case_no' => 'THH-2026-00010',
                'user' => $citizens[6], // Harishbhai Baria
                'category_id' => $catLegal?->id,
                'sub_category_id' => $subCasteCert?->id,
                'title' => 'આદિવાસી જાતિ પ્રમાણપત્ર અને પેઢીનામું બનાવવામાં સહાય (Caste Certificate & Pedhinama)',
                'description' => 'પુત્રના ૧૦મા ધોરણ બાદ પોલિટેકનિક પ્રવેશ માટે અનુસૂચિત જનજાતિ (ST) દાખલા અને વિજીલન્સ કમિશન વેરિફિકેશન માટે માર્ગદર્શન જોઈએ.',
                'urgency' => 'normal',
                'priority' => 'medium',
                'status' => Application::STATUS_CATEGORISED,
                'village_id' => $rozamVillage?->id,
                'lat' => 22.8398,
                'lng' => 74.2541,
                'assignee' => null,
                'created_at' => Carbon::now()->subDays(2),
                'resolved_at' => null,
                'milestones' => [
                    ['received', 'અરજી પ્રાપ્ત થઈ', 'Caste certificate help requested.'],
                    ['verification', 'દસ્તાવેજ યાદી ચકાસી', 'Checked 1950 prior ancestry documents and school LC.'],
                    ['categorised', 'કાનૂની અને મહેસૂલી માર્ગદર્શન', 'Categorised under Revenue & Tribal Vigilance.'],
                ],
                'documents' => [
                    ['Grandfather School Leaving Certificate 1948', 'documents/demo/lc_1948_00010.pdf', 'pending', null],
                ],
                'follow_ups' => [],
            ],
            [
                'case_no' => 'THH-2026-00011',
                'user' => $citizens[6], // Harishbhai Baria
                'category_id' => $catSchemes?->id,
                'sub_category_id' => $subPmKisan?->id,
                'title' => 'પીએમ કિસાન સન્માન નિધિ ડુપ્લિકેટ અરજી',
                'description' => 'અગાઉથી ચાલુ યોજનામાં બીજી વાર અરજી કરેલ હોવાથી પંચાયત દ્વારા નામ રદ કરવામાં આવ્યું હતું.',
                'urgency' => 'low',
                'priority' => 'low',
                'status' => Application::STATUS_REJECTED,
                'village_id' => $rozamVillage?->id,
                'lat' => 22.8398,
                'lng' => 74.2541,
                'assignee' => $sevakDahod,
                'created_at' => Carbon::now()->subDays(20),
                'resolved_at' => null,
                'milestones' => [
                    ['received', 'અરજી પ્રાપ્ત થઈ', 'PM-Kisan query.'],
                    ['verification', 'પોર્ટલ સ્ટેટસ ચકાસણી', 'Found already receiving benefits under family account with different Aadhaar.'],
                    ['rejected', 'ડુપ્લિકેટ અરજી હોવાથી અસ્વીકાર્ય', 'Citizen family already actively receiving installment under father name. Advised not to apply twice.'],
                ],
                'documents' => [],
                'follow_ups' => [],
            ],
            [
                'case_no' => 'THH-2026-00012',
                'user' => $citizens[0], // Sureshbhai Gamit
                'category_id' => $catVillage?->id,
                'sub_category_id' => $subStreetLight?->id,
                'title' => 'આહવા વૉર્ડ નં. ૩ માં વરસાદ બાદ સ્ટ્રીટલાઇટ ફરી બંધ થઈ ગઈ (Street Light Reopened)',
                'description' => 'ગયા મહિને રીપેર થયેલ સોલાર સ્ટ્રીટ લાઇટ બેટરીમાં શોર્ટ સર્કિટ થવાથી ફરી બંધ થઈ ગઈ છે. રાત્રે વન્ય પ્રાણીઓનો ભય રહે છે.',
                'urgency' => 'urgent',
                'priority' => 'high',
                'status' => Application::STATUS_REOPENED,
                'village_id' => $shamgahanVillage?->id,
                'lat' => 20.8123,
                'lng' => 73.7011,
                'assignee' => $sevakAhwa,
                'created_at' => Carbon::now()->subDays(40),
                'resolved_at' => null,
                'milestones' => [
                    ['received', 'પ્રારંભિક અરજી', 'Street lights repaired.'],
                    ['resolved', 'રિપેર પૂર્ણ', 'Technician replaced wire.'],
                    ['reopened', 'ફરીથી ખામી સર્જાતા પુનઃ ખોલેલ', 'Citizen reported new battery fault during heavy rain. Reopened for warranty replacement.'],
                ],
                'documents' => [
                    ['Defect Photo', 'documents/demo/solar_light_00012.jpg', 'pending', null],
                ],
                'follow_ups' => [
                    ['pending', Carbon::now()->addDays(1), 'Visit location with GEDA vendor electrician.'],
                ],
            ],
            [
                'case_no' => 'THH-2026-00013',
                'user' => $citizens[0], // Sureshbhai Gamit
                'category_id' => $catScholarships?->id,
                'sub_category_id' => $subHigherGrant?->id,
                'title' => 'જીપીએસસી (GPSC) વર્ગ-૧/૨ પરીક્ષા તૈયારી કોચિંગ સહાય યોજના',
                'description' => 'આદિવાસી વિદ્યાર્થીઓ માટે સરકાર દ્વારા ગાંધીનગર ખાતે અપાતા વિનામૂલ્યે તાલીમ વર્ગમાં પ્રવેશ માટે માર્ગદર્શન જોઈએ છે.',
                'urgency' => 'normal',
                'priority' => 'medium',
                'status' => Application::STATUS_ASSISTANCE,
                'village_id' => $shamgahanVillage?->id,
                'lat' => 20.8123,
                'lng' => 73.7011,
                'assignee' => $mentorEducation,
                'created_at' => Carbon::now()->subDays(6),
                'resolved_at' => null,
                'milestones' => [
                    ['received', 'અરજી પ્રાપ્ત થઈ', 'GPSC Coaching grant inquiry.'],
                    ['verification', 'સ્નાતક પરિણામ ચકાસ્યું', 'Verified 68% in graduation and ST certificate.'],
                    ['categorised', 'ઉચ્ચ શિક્ષણ માર્ગદર્શન', 'Mapped to Mentor Dr. Arvindbhai Rathwa.'],
                    ['assigned', 'શિક્ષણ મેન્ટરને સોંપાયેલ', 'Assigned to Dr. Arvindbhai Rathwa.'],
                    ['assistance', 'ઓનલાઇન પ્રવેશ ફોર્મ ભરાવ્યું', 'Mentorship session completed and SPIPA / Tribal Coaching entrance form submitted.'],
                ],
                'documents' => [
                    ['Graduation Marksheet', 'documents/demo/marksheet_00013.pdf', 'verified', $mentorEducation->id],
                ],
                'follow_ups' => [
                    ['pending', Carbon::now()->addDays(7), 'Share study timetable and mock test access.'],
                ],
            ],
            [
                'case_no' => 'THH-2026-00014',
                'user' => $citizens[5], // Shantaben Kotwal
                'category_id' => $catHealth?->id,
                'sub_category_id' => $subMaternal?->id,
                'title' => 'પીપલદહડ ફળિયામાં સગર્ભા માતા માટે મમતા કીટ અને પૌષ્ટિક આહાર સહાય',
                'description' => 'દૂરના પહાડી નેસડામાં રહેતી સગર્ભા માતાને નિયમિત આંગણવાડી પોષણ અને આયર્ન ગોળીઓ પહોંચતી નથી.',
                'urgency' => 'urgent',
                'priority' => 'high',
                'status' => Application::STATUS_VERIFICATION,
                'village_id' => $saputaraVillage?->id,
                'lat' => 20.8845,
                'lng' => 73.7820,
                'assignee' => $sevakAhwa,
                'created_at' => Carbon::now()->subDays(3),
                'resolved_at' => null,
                'milestones' => [
                    ['received', 'અરજી પ્રાપ્ત થઈ', 'Remote maternal nutrition assistance alert.'],
                    ['verification', 'આશા વર્કર સાથે સંકલન', 'Coordinating with Subir PHC ASHA worker for home visit.'],
                ],
                'documents' => [],
                'follow_ups' => [
                    ['pending', Carbon::now()->addDays(2), 'Deliver nutrition kit in person.'],
                ],
            ],
            [
                'case_no' => 'THH-2026-00015',
                'user' => $citizens[6], // Harishbhai Baria
                'category_id' => $catAgri?->id,
                'sub_category_id' => $subDrip?->id,
                'title' => 'ટપક સિંચાઈ પદ્ધતિ (Drip Irrigation) સબસિડી ફાઇલ તાલુકા પંચાયતમાં પેન્ડિંગ હોવા બાબત',
                'description' => '૨ હેક્ટર જમીનમાં ટપક પદ્ધતિ સ્થાપવા અરજી કરેલ છે. ૭૦% સરકારી સબસિડી મંજૂરી માટે તાલુકા વિસ્તરણ અધિકારીની મુલાકાત જરૂરી છે.',
                'urgency' => 'normal',
                'priority' => 'medium',
                'status' => Application::STATUS_ASSIGNED,
                'village_id' => $rozamVillage?->id,
                'lat' => 22.8398,
                'lng' => 74.2541,
                'assignee' => $mentorAgri,
                'created_at' => Carbon::now()->subDays(11),
                'resolved_at' => null,
                'milestones' => [
                    ['received', 'અરજી પ્રાપ્ત થઈ', 'Drip subsidy pending request.'],
                    ['verification', 'જીજીઆરસી રજિસ્ટ્રેશન ચકાસ્યું', 'Verified GGRC application number.'],
                    ['categorised', 'કૃષિ અને ખેતીવાડી સુવિધા', 'Assigned to Agri Mentor Kantibhai Dindor for technical scrutiny.'],
                    ['assigned', 'કૃષિ નિષ્ણાતને સોંપાયેલ', 'Assigned for liaison with Dahod taluka development office.'],
                ],
                'documents' => [
                    ['GGRC Application Receipt', 'documents/demo/ggrc_receipt_00015.pdf', 'verified', $mentorAgri->id],
                ],
                'follow_ups' => [
                    ['pending', Carbon::now()->addDays(3), 'Accompany farmer to Dahod Agriculture Office.'],
                ],
            ],
            [
                'case_no' => 'THH-2026-00016',
                'user' => $citizens[1], // Manishaben Rathwa
                'category_id' => $catForest?->id,
                'sub_category_id' => $subCommunityFra?->id,
                'title' => 'કવાંટ તાલુકાના પાનવડ ગામમાં સામુદાયિક વન અધિકાર (CFR) દાવો સ્વીકારાયો',
                'description' => 'ગામ સભા દ્વારા ૩૦૦ હેક્ટર જંગલ જમીન પર ગૌણ વનપેદાશ એકત્રીકરણ અને પરંપરાગત વન સંરક્ષણ હક્ક માટેનો દાવો સબ ડિવિઝનલ કમિટી (SDLC) સમક્ષ મંજૂર થયો.',
                'urgency' => 'normal',
                'priority' => 'high',
                'status' => Application::STATUS_RESOLVED,
                'village_id' => $kawantVillage?->id,
                'lat' => 22.1890,
                'lng' => 74.1100,
                'assignee' => $mentorLegal,
                'created_at' => Carbon::now()->subDays(60),
                'resolved_at' => Carbon::now()->subDays(5),
                'rating' => 5,
                'feedback' => 'એડવોકેટ જ્યોત્સ્નાબેનના માર્ગદર્શનથી આખા ગામના વન હક્કો કાયદેસર થયા. ઐતિહાસિક વિજય!',
                'milestones' => [
                    ['received', 'સામુદાયિક વન હક્ક અરજી', 'Community Forest Rights claim initiated by Panvad Gram Sabha.'],
                    ['verification', 'નકશા અને પુરાવા તૈયાર કર્યા', 'Surveyed boundaries and GPS points with Forest Rights Committee.'],
                    ['categorised', 'વન અધિકાર અધિનિયમ', 'Special CFR cell activated.'],
                    ['assigned', 'કાનૂની સલાહકારને સોંપાયેલ', 'Assigned to Adv. Jyotsnaben Chaudhary.'],
                    ['assistance', 'એસડીએલસી સમક્ષ સુનાવણી', 'Represented community before SDLC committee.'],
                    ['followUp', 'ડીએલસી અંતિમ મંજૂરી', 'DLC committee Chhota Udepur passed final resolution.'],
                    ['resolved', 'સામુદાયિક વન અધિકાર પત્રક સુપરત', 'Handed over official Community Forest Rights title deed to Gram Sabha.'],
                ],
                'documents' => [
                    ['Gram Sabha CFR Resolution Copy', 'documents/demo/panvad_cfr_resolution.pdf', 'verified', $mentorLegal->id],
                    ['Official CFR Title Deed (Sanad)', 'documents/demo/panvad_cfr_sanad.pdf', 'verified', $mentorLegal->id],
                ],
                'follow_ups' => [
                    ['done', Carbon::now()->subDays(4), 'Handover of certified deed copies to Sarpanch.'],
                ],
            ],
        ];

        foreach ($applicationsData as $appData) {
            $application = Application::updateOrCreate(
                ['case_no' => $appData['case_no']],
                [
                    'user_id' => $appData['user']->id,
                    'category_id' => $appData['category_id'],
                    'sub_category_id' => $appData['sub_category_id'],
                    'title' => $appData['title'],
                    'description' => $appData['description'],
                    'urgency' => $appData['urgency'],
                    'priority' => $appData['priority'],
                    'status' => $appData['status'],
                    'village_id' => $appData['village_id'],
                    'lat' => $appData['lat'],
                    'lng' => $appData['lng'],
                    'current_assignee_id' => $appData['assignee']?->id,
                    'sla_due_at' => Carbon::now()->addDays(7),
                    'resolved_at' => $appData['resolved_at'],
                    'rating' => $appData['rating'] ?? null,
                    'feedback' => $appData['feedback'] ?? null,
                    'created_at' => $appData['created_at'],
                    'updated_at' => $appData['resolved_at'] ?? $appData['created_at'],
                ]
            );

            // Timeline Milestones
            foreach ($appData['milestones'] as $idx => [$milestoneStatus, $titleGu, $bodyEn]) {
                ApplicationTimelineEvent::firstOrCreate(
                    [
                        'application_id' => $application->id,
                        'event_type' => 'status_changed',
                        'to_status' => $milestoneStatus,
                    ],
                    [
                        'from_status' => $idx > 0 ? $appData['milestones'][$idx - 1][0] : null,
                        'title_key' => $titleGu,
                        'body' => $bodyEn,
                        'actor_id' => $appData['assignee']->id ?? $superAdmin->id,
                        'actor_role' => $appData['assignee'] ? 'staff' : 'admin',
                        'visibility' => 'public',
                        'created_at' => $appData['created_at']->copy()->addDays($idx * 2),
                    ]
                );
            }

            // Documents
            foreach ($appData['documents'] as [$type, $path, $docStatus, $verifiedBy]) {
                ApplicationDocument::firstOrCreate(
                    [
                        'application_id' => $application->id,
                        'type' => $type,
                    ],
                    [
                        'path' => $path,
                        'status' => $docStatus,
                        'verified_by' => $verifiedBy,
                    ]
                );
            }

            // Assignments
            if ($appData['assignee']) {
                ApplicationAssignment::firstOrCreate(
                    [
                        'application_id' => $application->id,
                        'assignee_id' => $appData['assignee']->id,
                    ],
                    [
                        'assignee_type' => 'staff',
                        'assigned_by' => $superAdmin->id,
                        'accepted_at' => $appData['created_at']->copy()->addHours(4),
                        'created_at' => $appData['created_at']->copy()->addHours(2),
                    ]
                );
            }

            // Follow-ups
            foreach ($appData['follow_ups'] as [$fStatus, $scheduledFor, $notes]) {
                FollowUp::firstOrCreate(
                    [
                        'application_id' => $application->id,
                        'notes' => $notes,
                    ],
                    [
                        'scheduled_for' => $scheduledFor,
                        'assigned_to' => $appData['assignee']->id ?? $superAdmin->id,
                        'status' => $fStatus,
                        'done_at' => $fStatus === 'done' ? $scheduledFor : null,
                    ]
                );
            }

            // Internal Message
            ApplicationMessage::firstOrCreate(
                [
                    'application_id' => $application->id,
                    'body' => 'Internal check: verified citizen mobile contact and ground situation with local Gram Sevak.',
                ],
                [
                    'sender_id' => $appData['assignee']->id ?? $superAdmin->id,
                    'created_at' => $appData['created_at']->copy()->addHours(6),
                ]
            );

            // Audit Log
            AuditLog::firstOrCreate(
                [
                    'action' => 'application_seeded',
                    'subject_type' => Application::class,
                    'subject_id' => $application->id,
                ],
                [
                    'actor_id' => $superAdmin->id,
                    'before' => null,
                    'after' => ['case_no' => $application->case_no, 'status' => $application->status],
                    'ip' => '127.0.0.1',
                    'user_agent' => 'Seeder/DemoData',
                    'created_at' => $application->created_at,
                ]
            );
        }

        // 7. Content & Community Modules Seeding

        // Schemes
        $schemesData = [
            [
                'slug' => 'pm_van_dhan_yojana',
                'is_published' => true,
                'eligibility_rules' => [
                    'community' => ['Gamit ST', 'Vasava ST', 'Rathwa ST', 'Bhil ST', 'Kotwalia ST', 'Tadvi ST'],
                    'min_age' => 18,
                    'income_category' => ['below_poverty_line', 'antodaya'],
                ],
                'benefits' => [
                    'title' => 'પ્રધાનમંત્રી વન ધન યોજના (PM Van Dhan Yojana)',
                    'grant' => 'Rs 15,00,000 grant per Van Dhan Vikas Kendra',
                    'training' => 'Free skill development for minor forest produce value addition.',
                ],
                'required_documents' => ['Aadhaar Card', 'Caste Certificate', 'Bank Passbook', 'SHG Membership Proof'],
                'process_steps' => [
                    'Form a Self-Help Group of 20-30 tribal gatherers',
                    'Submit proposal to District Forest Officer / TRIFED cell',
                    'Sanction of working capital grant and equipment training',
                ],
            ],
            [
                'slug' => 'ayushman_bharat_tribal_card',
                'is_published' => true,
                'eligibility_rules' => [
                    'income_category' => ['below_poverty_line', 'antodaya'],
                ],
                'benefits' => [
                    'title' => 'આયુષ્માન ભારત - મા અમૃતમ યોજના (Ayushman Bharat PMJAY-MA)',
                    'coverage' => 'Cashless medical treatment up to Rs 10,00,000 per family per year.',
                    'network' => 'Empanelled government and private specialty hospitals.',
                ],
                'required_documents' => ['Ration Card', 'Aadhaar Card', 'Income Certificate'],
                'process_steps' => [
                    'Visit nearest e-Gram or CHC health kiosk with ration card',
                    'Biometric e-KYC authentication',
                    'Instant PVC golden card generation',
                ],
            ],
            [
                'slug' => 'solar_irrigation_kusum',
                'is_published' => true,
                'eligibility_rules' => [
                    'community' => ['ST'],
                    'has_borewell' => true,
                ],
                'benefits' => [
                    'title' => 'સૂર્યશક્તિ કિસાન સોલાર સિંચાઈ યોજના (PM KUSUM Solar Pump)',
                    'subsidy' => '90% government subsidy for tribal farmers.',
                    'capacity' => '3 HP to 7.5 HP solar DC pump installation.',
                ],
                'required_documents' => ['7/12 & 8-A Land Record', 'Caste Certificate', 'Aadhaar Card', 'Water Source Proof'],
                'process_steps' => [
                    'Apply online via GGRC portal or Taluka Agri office',
                    'Field physical survey by GGRC engineer',
                    'Beneficiary contribution payment (10%)',
                    'Equipment installation and testing',
                ],
            ],
            [
                'slug' => 'vidyasadhana_bicycle',
                'is_published' => true,
                'eligibility_rules' => [
                    'gender' => 'female',
                    'min_age' => 13,
                    'max_age' => 16,
                ],
                'benefits' => [
                    'title' => 'સરસ્વતી સાધના સાયકલ યોજના (Free Bicycle for Tribal Girls)',
                    'benefit' => 'Brand new bicycle for high school commute (Standard 9).',
                ],
                'required_documents' => ['Student Bonafide', 'Parent Caste Certificate', 'Ration Card'],
                'process_steps' => [
                    'School headmaster submits compiled list to Taluka Education Officer',
                    'Distribution ceremony at Taluka headquarter',
                ],
            ],
        ];

        foreach ($schemesData as $sData) {
            Scheme::updateOrCreate(
                ['slug' => $sData['slug']],
                [
                    'is_published' => $sData['is_published'],
                    'eligibility_rules' => $sData['eligibility_rules'],
                    'benefits' => $sData['benefits'],
                    'required_documents' => $sData['required_documents'],
                    'process_steps' => $sData['process_steps'],
                ]
            );
        }

        // Scholarships
        $scholarshipsData = [
            [
                'slug' => 'st_post_matric_scholarship',
                'amount' => 15000.00,
                'deadline_at' => Carbon::now()->addMonths(2),
                'is_published' => true,
                'eligibility_rules' => [
                    'community' => ['ST'],
                    'annual_income_limit' => 250000,
                ],
            ],
            [
                'slug' => 'st_higher_education_foreign_grant',
                'amount' => 1500000.00,
                'deadline_at' => Carbon::now()->addMonths(4),
                'is_published' => true,
                'eligibility_rules' => [
                    'community' => ['ST'],
                    'minimum_marks' => 60,
                ],
            ],
            [
                'slug' => 'vidyasadhana_st_girl_stipend',
                'amount' => 6000.00,
                'deadline_at' => Carbon::now()->addMonths(1),
                'is_published' => true,
                'eligibility_rules' => [
                    'gender' => 'female',
                    'grade' => [9, 10, 11, 12],
                ],
            ],
        ];

        foreach ($scholarshipsData as $scData) {
            Scholarship::updateOrCreate(
                ['slug' => $scData['slug']],
                [
                    'amount' => $scData['amount'],
                    'deadline_at' => $scData['deadline_at'],
                    'is_published' => $scData['is_published'],
                    'eligibility_rules' => $scData['eligibility_rules'],
                ]
            );
        }

        // Job Postings
        $jobPostings = [
            [
                'title' => 'ગ્રામીણ સમુદાય સહાયક (Village Community Mobilizer)',
                'company' => 'GGVT Dang Field Mission',
                'location' => 'Ahwa, Dang',
                'salary_range' => '₹18,000 - ₹24,000 / month',
                'requirements' => ['BSW / MSW degree', 'Gujarati & Dangi dialect fluency', 'Two-wheeler driving license'],
                'deadline_at' => Carbon::now()->addDays(20),
                'is_active' => true,
            ],
            [
                'title' => 'કૃષિ વિસ્તરણ ટેકનિશિયન (Agri-Extension Technician)',
                'company' => 'Krishi Vigyan Kendra Dahod',
                'location' => 'Garbada, Dahod',
                'salary_range' => '₹20,000 - ₹28,000 / month',
                'requirements' => ['Diploma / B.Sc. Agriculture', 'Soil testing experience', 'Tribal community engagement experience'],
                'deadline_at' => Carbon::now()->addDays(25),
                'is_active' => true,
            ],
            [
                'title' => 'આઈટીઆઈ ઇલેક્ટ્રિશિયન એપ્રેન્ટિસ (ITI Apprentice)',
                'company' => 'Gujarat State Electricity Corp (Ukai Hydro Unit)',
                'location' => 'Songadh, Tapi',
                'salary_range' => '₹12,500 / month stipend',
                'requirements' => ['ITI Electrician / Wireman pass', 'Age 18-25 years', 'Valid ST category certificate'],
                'deadline_at' => Carbon::now()->addDays(15),
                'is_active' => true,
            ],
        ];

        foreach ($jobPostings as $jData) {
            JobPosting::updateOrCreate(
                ['title' => $jData['title'], 'company' => $jData['company']],
                $jData
            );
        }

        // Libraries
        $libraries = [
            [
                'name' => 'એકલવ્ય ડિજિટલ જ્ઞાન કેન્દ્ર અને પુસ્તકાલય (Eklavya Knowledge Library)',
                'district_id' => $dangDistrict?->id,
                'taluka_id' => $ahwaTaluka?->id,
                'village_id' => $ahwaVillage?->id,
                'address' => 'Near Old Civil Hospital, Civil Lines, Ahwa, Dist. Dang',
                'contact_person' => 'Shri Ramanbhai Patel',
                'phone' => '02631220011',
                'total_books' => 4500,
                'computers_count' => 12,
                'lat' => 20.7583,
                'lng' => 73.6872,
                'is_active' => true,
            ],
            [
                'name' => 'દાહોદ યુવા રિસોર્સ સેન્ટર (Dahod Youth Resource Library)',
                'district_id' => $dahodDistrict?->id,
                'taluka_id' => $garbadaTaluka?->id,
                'village_id' => $gangardiVillage?->id,
                'address' => 'Panchayat Bhavan Road, Gangardi, Dahod',
                'contact_person' => 'Kailasben Vasava',
                'phone' => '9876500004',
                'total_books' => 2200,
                'computers_count' => 6,
                'lat' => 22.7150,
                'lng' => 74.3200,
                'is_active' => true,
            ],
        ];

        foreach ($libraries as $lData) {
            Library::updateOrCreate(
                ['name' => $lData['name']],
                $lData
            );
        }

        // Mock Tests & Questions
        $mockTest = MockTest::updateOrCreate(
            ['title' => 'જીપીએસસી આદિજાતિ વિકાસ અધિકારી (TDO) મોક ટેસ્ટ - ૧'],
            [
                'category' => 'competitive_exam',
                'duration_minutes' => 60,
                'total_marks' => 50,
                'is_active' => true,
            ]
        );

        $mockQuestions = [
            [
                'question_text' => 'વન અધિકાર અધિનિયમ (FRA) કયા વર્ષમાં પસાર કરવામાં આવ્યો હતો?',
                'options' => ['૨૦૦૪', '૨૦૦૬', '૨૦૦૮', '૨૦૧૦'],
                'correct_answer' => '૨૦૦૬',
                'explanation' => 'The Scheduled Tribes and Other Traditional Forest Dwellers (Recognition of Forest Rights) Act was enacted in 2006.',
            ],
            [
                'question_text' => 'ગુજરાતમાં સૌથી વધુ વન વિસ્તાર ધરાવતો જિલ્લો કયો છે?',
                'options' => ['નર્મદા', 'ડાંગ', 'તાપી', 'સાબરકાંઠા'],
                'correct_answer' => 'ડાંગ',
                'explanation' => 'Dang district has the highest percentage of forest cover in Gujarat (over 77%).',
            ],
            [
                'question_text' => 'ભારતીય બંધારણની કઈ અનુસૂચિ આદિવાસી વિસ્તારોના વહીવટ સાથે જોડાયેલ છે?',
                'options' => ['ત્રીજી અનુસૂચિ', 'પાંચમી અનુસૂચિ', 'આઠમી અનુસૂચિ', 'અગિયારમી અનુસૂચિ'],
                'correct_answer' => 'પાંચમી અનુસૂચિ',
                'explanation' => 'The Fifth Schedule of the Indian Constitution provides administration and control for Scheduled Areas and Scheduled Tribes.',
            ],
        ];

        foreach ($mockQuestions as $qData) {
            MockTestQuestion::updateOrCreate(
                ['mock_test_id' => $mockTest->id, 'question_text' => $qData['question_text']],
                $qData
            );
        }

        // Study Materials
        $studyMaterials = [
            [
                'title' => 'વન અધિકાર કાયદો (FRA 2006) સરળ ગુજરાતી માર્ગદર્શિકા PDF',
                'category' => 'legal_guidance',
                'file_path' => 'materials/fra_guideline_gujarati.pdf',
                'file_type' => 'pdf',
                'is_active' => true,
            ],
            [
                'title' => 'ગુજરાત સામાન્ય જ્ઞાન (આદિવાસી સંસ્કૃતિ અને ઈતિહાસ વિશેષાંક)',
                'category' => 'general_knowledge',
                'file_path' => 'materials/tribal_heritage_gk.pdf',
                'file_type' => 'pdf',
                'is_active' => true,
            ],
        ];

        foreach ($studyMaterials as $smData) {
            StudyMaterial::updateOrCreate(
                ['title' => $smData['title']],
                $smData
            );
        }

        // Business Ideas
        $businessIdeas = [
            [
                'title' => 'વાંસ હસ્તકલા અને ઇકો-ફ્રેન્ડલી ફર્નિચર ઉત્પાદન (Bamboo Handicrafts)',
                'category' => 'cottage_industry',
                'investment_range' => '₹50,000 - ₹1,50,000',
                'description' => 'ડાંગ અને તાપી જિલ્લામાં વિપુલ માત્રામાં વાંસ ઉપલબ્ધ છે. કુશળ કારીગરો દ્વારા ઇકો-ફ્રેન્ડલી લેમ્પશેડ, બાસ્કેટ, અને ફર્નિચર બનાવી પ્રવાસન સ્થળોએ વેચાણ.',
                'market_potential' => 'High demand in Saputara resorts, Surat, and urban craft exhibitions.',
                'is_active' => true,
            ],
            [
                'title' => 'જંગલી શુદ્ધ મધ પ્રોસેસિંગ અને બ્રાન્ડિંગ (Wild Forest Honey)',
                'category' => 'organic_food',
                'investment_range' => '₹75,000 - ₹2,00,000',
                'description' => 'કુદરતી જંગલમાંથી મેળવેલ ઔષધીય મધનું વૈજ્ઞાનિક ફિલ્ટરેશન અને સ્વચ્છ કાચની બોટલમાં પેકિંગ કરી સહકારી ધોરણે વેચાણ.',
                'market_potential' => 'Premium pricing in organic markets across Gujarat and export opportunities.',
                'is_active' => true,
            ],
        ];

        foreach ($businessIdeas as $bData) {
            BusinessIdea::updateOrCreate(
                ['title' => $bData['title']],
                $bData
            );
        }

        // Health Camps & Hospitals
        $hospitals = [
            [
                'name' => 'જનરલ સિવિલ હોસ્પિટલ આહવા (Civil Hospital Ahwa)',
                'district_id' => $dangDistrict?->id,
                'address' => 'Hospital Road, Ahwa, Dang 394710',
                'phone' => '02631220234',
                'emergency_contact' => '108',
                'has_blood_bank' => true,
                'lat' => 20.7583,
                'lng' => 73.6872,
                'is_active' => true,
            ],
            [
                'name' => 'ઝાયડસ સિવિલ હોસ્પિટલ દાહોદ (Zydus Medical College & Hospital)',
                'district_id' => $dahodDistrict?->id,
                'address' => 'Indore Highway, Dahod 389151',
                'phone' => '02673245678',
                'emergency_contact' => '108',
                'has_blood_bank' => true,
                'lat' => 22.8398,
                'lng' => 74.2541,
                'is_active' => true,
            ],
            [
                'name' => 'જનરલ હોસ્પિટલ વ્યારા (General Hospital Vyara)',
                'district_id' => $tapiDistrict?->id,
                'address' => 'Station Road, Vyara, Tapi 394650',
                'phone' => '02626220145',
                'emergency_contact' => '108',
                'has_blood_bank' => true,
                'lat' => 21.1120,
                'lng' => 73.3980,
                'is_active' => true,
            ],
        ];

        $createdHospitals = [];
        foreach ($hospitals as $hData) {
            $createdHospitals[] = Hospital::updateOrCreate(
                ['name' => $hData['name']],
                $hData
            );
        }

        HealthCamp::updateOrCreate(
            ['title' => 'વિશેષ સિકલ સેલ એનિમિયા અને બાળ આરોગ્ય શિબિર'],
            [
                'organizer' => 'GGVT Medical Wing & Red Cross',
                'district_id' => $dangDistrict?->id,
                'village_id' => $shamgahanVillage?->id,
                'address' => 'Community Hall, Shamgahan Village, Ahwa, Dang',
                'scheduled_at' => Carbon::now()->addDays(6),
                'doctors_specialties' => ['Pediatrics', 'Hematology', 'General Medicine', 'Gynecology'],
                'is_active' => true,
            ]
        );

        // Blood Requests
        BloodRequest::updateOrCreate(
            ['patient_name' => 'Prakashbhai Gamit'],
            [
                'blood_group' => 'O-',
                'hospital_id' => $createdHospitals[2]->id ?? null,
                'units_required' => 2,
                'urgency' => 'urgent',
                'status' => 'active',
                'contact_phone' => '9825000015',
            ]
        );

        BloodRequest::updateOrCreate(
            ['patient_name' => 'Shantibhai Vasava'],
            [
                'blood_group' => 'B+',
                'hospital_id' => $createdHospitals[0]->id ?? null,
                'units_required' => 1,
                'urgency' => 'normal',
                'status' => 'fulfilled',
                'contact_phone' => '9825000011',
            ]
        );

        // Sakhi Circles (SHGs)
        $sakhiCircles = [
            [
                'name' => 'ઉષા આદિવાસી મહિલા સખી મંડળ (Usha Sakhi Mandal)',
                'village_id' => $ahwaVillage?->id,
                'leader_name' => 'Jasumatiben Bhoya',
                'leader_phone' => '9876500021',
                'members_count' => 14,
                'activities' => ['Organic Turmeric Processing', 'Bamboo Crafts', 'Grain Banking'],
                'is_active' => true,
            ],
            [
                'name' => 'જય આદિવાસી પીઠોરા બહેનો ગ્રુપ (Jai Adivasi Pithora Mandal)',
                'village_id' => $kawantVillage?->id,
                'leader_name' => 'Manishaben Dilipbhai Rathwa',
                'leader_phone' => '9825000012',
                'members_count' => 12,
                'activities' => ['Pithora Paintings', 'Handloom Stoles', 'Traditional Beads Jewelry'],
                'is_active' => true,
            ],
        ];

        foreach ($sakhiCircles as $skData) {
            SakhiCircle::updateOrCreate(
                ['name' => $skData['name']],
                $skData
            );
        }

        // Village Infrastructure Reports
        $villageReports = [
            [
                'village_id' => $shamgahanVillage?->id,
                'user_id' => $citizens[0]->id,
                'title' => 'મુખ્ય માર્ગ પર વરસાદી પાણીના નિકાલની ગટર તૂટી ગઈ છે',
                'category' => 'sanitation',
                'description' => 'શામગહાન પ્રવેશ દ્વાર પાસે ગટર બ્લોક થવાથી રસ્તા પર ગંદુ પાણી ભરાય છે. મચ્છરોનો ઉપદ્રવ વધ્યો છે.',
                'status' => 'pending',
                'lat' => 20.8123,
                'lng' => 73.7011,
            ],
            [
                'village_id' => $dumkhalVillage?->id,
                'user_id' => $citizens[2]->id,
                'title' => 'પ્રાથમિક શાળા પરિસરમાં પીવાના પાણીનો હેન્ડપંપ રીપેરિંગ',
                'category' => 'water',
                'description' => 'શાળાના ૧૨૦ બાળકો માટે હેન્ડપંપ ૧ સપ્તાહથી બંધ છે.',
                'status' => 'action_taken',
                'lat' => 21.5790,
                'lng' => 73.7100,
            ],
        ];

        foreach ($villageReports as $vrData) {
            VillageReport::updateOrCreate(
                ['title' => $vrData['title'], 'village_id' => $vrData['village_id']],
                $vrData
            );
        }

        // 8. CMS, Tiles, Banners, FAQs, Static Pages & System Settings

        // Home Tiles
        $homeTiles = [
            ['key' => 'request_help', 'icon' => 'HandHeart', 'target_route' => '/help/new', 'sort_order' => 1],
            ['key' => 'track_case', 'icon' => 'Search', 'target_route' => '/track', 'sort_order' => 2],
            ['key' => 'schemes', 'icon' => 'Award', 'target_route' => '/schemes', 'sort_order' => 3],
            ['key' => 'scholarships', 'icon' => 'GraduationCap', 'target_route' => '/scholarships', 'sort_order' => 4],
            ['key' => 'health', 'icon' => 'HeartPulse', 'target_route' => '/health', 'sort_order' => 5],
            ['key' => 'jobs', 'icon' => 'Briefcase', 'target_route' => '/jobs', 'sort_order' => 6],
            ['key' => 'mentorship', 'icon' => 'Users', 'target_route' => '/mentors', 'sort_order' => 7],
            ['key' => 'sakhi', 'icon' => 'Sparkles', 'target_route' => '/sakhi', 'sort_order' => 8],
        ];

        foreach ($homeTiles as $htData) {
            HomeTile::updateOrCreate(
                ['key' => $htData['key']],
                [
                    'icon' => $htData['icon'],
                    'target_route' => $htData['target_route'],
                    'sort_order' => $htData['sort_order'],
                    'is_enabled' => true,
                ]
            );
        }

        // Banners
        $banners = [
            [
                'title_key' => 'સહાય અરજી હવે ઓનલાઇન અને ઑફલાઇન ઉપલબ્ધ',
                'image_url' => '/assets/banners/banner_tribal_help.jpg',
                'link_route' => '/help/new',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title_key' => 'વન અધિકાર અધિનિયમ અને જમીન હક્ક મફત માર્ગદર્શન શિબિર',
                'image_url' => '/assets/banners/banner_fra_guidance.jpg',
                'link_route' => '/schemes',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $bData) {
            Banner::updateOrCreate(
                ['title_key' => $bData['title_key']],
                $bData
            );
        }

        // FAQs
        $faqs = [
            [
                'category_slug' => 'general',
                'question_key' => 'આદિવાસી સહાયક હાથ પોર્ટલ શું છે?',
                'answer_key' => 'ગ્લોબલ ગ્રામીણ વિકાસ ટ્રસ્ટ (GGVT) દ્વારા આદિવાસી નાગરિકો માટે શિક્ષણ, આરોગ્ય, સરકારી યોજનાઓ અને ગામડાની પાયાની સમસ્યાઓના ઉકેલ માટેનું વિશ્વસનીય ડિજિટલ મંચ છે.',
                'sort_order' => 1,
            ],
            [
                'category_slug' => 'applications',
                'question_key' => 'હું અરજીની સ્થિતિ કેવી રીતે ચકાસી શકું?',
                'answer_key' => 'તમારી પાસે રહેલ કેસ નંબર (દા.ત. THH-2026-00001) મોબાઇલ એપ્લિકેશન અથવા વેબ પોર્ટલના ટ્રેકર બોક્સમાં દાખલ કરીને સમયરેખા સહિત પ્રગતિ જોઈ શકો છો.',
                'sort_order' => 2,
            ],
            [
                'category_slug' => 'applications',
                'question_key' => 'શું અરજી કરવા માટે કોઈ ફી આપવી પડે છે?',
                'answer_key' => 'ના, Tribal Helping Hand તરફથી આપવામાં આવતી તમામ સહાય, માર્ગદર્શન અને સુવિધા સંપૂર્ણપણે વિનામૂલ્યે છે.',
                'sort_order' => 3,
            ],
            [
                'category_slug' => 'offline',
                'question_key' => 'ઇન્ટરનેટ ન હોય ત્યારે શું હું અરજી કરી શકું?',
                'answer_key' => 'હા, મોબાઇલ એપ સંપૂર્ણપણે ઑફલાઇન કાર્ય કરે છે. નેટવર્ક આવતા જ તમારી અરજી આપમેળે સર્વર સાથે સિંક થઈ જશે.',
                'sort_order' => 4,
            ],
        ];

        foreach ($faqs as $fData) {
            Faq::updateOrCreate(
                ['question_key' => $fData['question_key']],
                $fData
            );
        }

        // Static Pages
        $staticPages = [
            [
                'slug' => 'about-us',
                'title_key' => 'અમારા વિશે (About GGVT)',
                'content_key' => 'ગ્લોબલ ગ્રામીણ વિકાસ ટ્રસ્ટ (GGVT) ગુજરાતના આદિવાસી પટ્ટામાં શિક્ષણ, કાનૂની અધિકારો અને જીવનધોરણ ઉન્નત કરવા માટે સમર્પિત બિન-રાજકીય સંસ્થા છે.',
                'is_active' => true,
            ],
            [
                'slug' => 'privacy-policy',
                'title_key' => 'ગોપનીયતા નીતિ (Privacy Policy)',
                'content_key' => 'તમારા વ્યક્તિગત દસ્તાવેજો અને માહિતી માત્ર સરકારી કચેરી સંકલન અને સહાય હેતુ માટે જ સુરક્ષિત રાખવામાં આવે છે.',
                'is_active' => true,
            ],
            [
                'slug' => 'terms-conditions',
                'title_key' => 'નિયમો અને શરતો (Terms & Conditions)',
                'content_key' => 'પોર્ટલનો ઉપયોગ સાચી અને ચોક્કસ માહિતી સાથે જનસેવા હેતુથી કરવો આવશ્યક છે.',
                'is_active' => true,
            ],
        ];

        foreach ($staticPages as $spData) {
            StaticPage::updateOrCreate(
                ['slug' => $spData['slug']],
                $spData
            );
        }

        // System Settings
        $settings = [
            ['key' => 'app_name_en', 'group' => 'general', 'value' => 'Tribal Helping Hand'],
            ['key' => 'app_name_gu', 'group' => 'general', 'value' => 'આદિવાસી સહાયક હાથ'],
            ['key' => 'trust_name', 'group' => 'general', 'value' => 'Global Gramin Vikas Trust (GGVT)'],
            ['key' => 'helpline_phone', 'group' => 'support', 'value' => '+91 2631 220050'],
            ['key' => 'emergency_phone', 'group' => 'support', 'value' => '+91 98765 00001'],
            ['key' => 'support_email', 'group' => 'support', 'value' => 'helpdesk@ggvt.org'],
            ['key' => 'sla_default_days', 'group' => 'workflow', 'value' => 7],
        ];

        foreach ($settings as $sData) {
            Setting::updateOrCreate(
                ['key' => $sData['key']],
                [
                    'group' => $sData['group'],
                    'value' => $sData['value'],
                ]
            );
        }
    }
}
