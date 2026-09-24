<?php

use App\Domains\Users\Models\District;
use App\Domains\Users\Models\Taluka;
use App\Domains\Users\Models\Village;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $navsariData = [
            'name_en' => 'Navsari',
            'name_gu' => 'નવસારી',
            'code' => 'NAVSARI',
            'talukas' => [
                [
                    'name_en' => 'Navsari Rural & City',
                    'name_gu' => 'નવસારી ગ્રામ્ય અને શહેર',
                    'code' => 'NAV_RUR_CITY',
                    'villages' => [
                        ['name_en' => 'Kaliawadi', 'name_gu' => 'કાલિયાવાડી', 'pincode' => '396445', 'lat' => 20.9467, 'lng' => 72.9276],
                        ['name_en' => 'Vijalpore', 'name_gu' => 'વિજલપોર', 'pincode' => '396445', 'lat' => 20.9328, 'lng' => 72.9372],
                        ['name_en' => 'Navsari City', 'name_gu' => 'નવસારી શહેર', 'pincode' => '396445', 'lat' => 20.9500, 'lng' => 72.9300],
                        ['name_en' => 'Khadsupa', 'name_gu' => 'ખડસુપા', 'pincode' => '396433', 'lat' => 20.8845, 'lng' => 72.9812],
                        ['name_en' => 'Sisodra', 'name_gu' => 'સિસોદ્રા', 'pincode' => '396463', 'lat' => 20.9120, 'lng' => 72.9540],
                    ],
                ],
                [
                    'name_en' => 'Chikhli Cluster',
                    'name_gu' => 'ચીખલી વિસ્તાર',
                    'code' => 'CHIKHLI',
                    'villages' => [
                        ['name_en' => 'Chikhli', 'name_gu' => 'ચીખલી', 'pincode' => '396521', 'lat' => 20.7554, 'lng' => 73.0622],
                        ['name_en' => 'Alipore', 'name_gu' => 'અલીપોર', 'pincode' => '396409', 'lat' => 20.7656, 'lng' => 72.9961],
                        ['name_en' => 'Samarvapi', 'name_gu' => 'સમરવાપી', 'pincode' => '396521', 'lat' => 20.7420, 'lng' => 73.0510],
                        ['name_en' => 'Rankuwa', 'name_gu' => 'રાંકુવા', 'pincode' => '396560', 'lat' => 20.8140, 'lng' => 73.1250],
                    ],
                ],
                [
                    'name_en' => 'Jalalpore Coastal Belt',
                    'name_gu' => 'જલાલપોર દરિયાકાંઠો',
                    'code' => 'JALALPORE',
                    'villages' => [
                        ['name_en' => 'Jalalpore', 'name_gu' => 'જલાલપોર', 'pincode' => '396421', 'lat' => 20.9490, 'lng' => 72.8940],
                        ['name_en' => 'Maroli', 'name_gu' => 'મરોલી', 'pincode' => '396436', 'lat' => 20.9210, 'lng' => 72.8680],
                        ['name_en' => 'Dandi', 'name_gu' => 'દાંડી', 'pincode' => '396439', 'lat' => 20.8900, 'lng' => 72.7950],
                        ['name_en' => 'Ubhrat', 'name_gu' => 'ઉભરાટ', 'pincode' => '396436', 'lat' => 20.9630, 'lng' => 72.7840],
                    ],
                ],
                [
                    'name_en' => 'Gandevi & Bilimora',
                    'name_gu' => 'ગણદેવી અને બીલીમોરા',
                    'code' => 'GANDEVI',
                    'villages' => [
                        ['name_en' => 'Bilimora', 'name_gu' => 'બીલીમોરા', 'pincode' => '396321', 'lat' => 20.7583, 'lng' => 72.9555],
                        ['name_en' => 'Gandevi', 'name_gu' => 'ગણદેવી', 'pincode' => '396360', 'lat' => 20.8122, 'lng' => 72.9877],
                        ['name_en' => 'Antalia', 'name_gu' => 'અંતાલિયા', 'pincode' => '396325', 'lat' => 20.7690, 'lng' => 72.9610],
                        ['name_en' => 'Devdha', 'name_gu' => 'દેવધા', 'pincode' => '396360', 'lat' => 20.8350, 'lng' => 72.9720],
                    ],
                ],
                [
                    'name_en' => 'Vansda Tribal Welfare Belt',
                    'name_gu' => 'વાંસદા આદિવાસી વિસ્તાર',
                    'code' => 'VANSDA',
                    'villages' => [
                        ['name_en' => 'Vansda', 'name_gu' => 'વાંસદા', 'pincode' => '396580', 'lat' => 20.7634, 'lng' => 73.3672],
                        ['name_en' => 'Unai', 'name_gu' => 'ઉનાઈ', 'pincode' => '396590', 'lat' => 20.8490, 'lng' => 73.3320],
                        ['name_en' => 'Anaval', 'name_gu' => 'અનાવલ', 'pincode' => '396510', 'lat' => 20.8710, 'lng' => 73.3050],
                        ['name_en' => 'Dharampur Border', 'name_gu' => 'ધરમપુર સીમા', 'pincode' => '396580', 'lat' => 20.7100, 'lng' => 73.3200],
                    ],
                ],
                [
                    'name_en' => 'Khergam Taluka',
                    'name_gu' => 'ખેરગામ તાલુકો',
                    'code' => 'KHERGAM',
                    'villages' => [
                        ['name_en' => 'Khergam', 'name_gu' => 'ખેરગામ', 'pincode' => '396040', 'lat' => 20.6558, 'lng' => 73.0805],
                        ['name_en' => 'Nadagdhari', 'name_gu' => 'નાંદગધરી', 'pincode' => '396040', 'lat' => 20.6720, 'lng' => 73.0640],
                        ['name_en' => 'Chimanpada', 'name_gu' => 'ચીમનપાડા', 'pincode' => '396040', 'lat' => 20.6410, 'lng' => 73.1120],
                    ],
                ],
                [
                    'name_en' => 'Mahuva Cluster',
                    'name_gu' => 'મહુવા ક્લસ્ટર',
                    'code' => 'MAHUVA',
                    'villages' => [
                        ['name_en' => 'Mahuva Road', 'name_gu' => 'મહુવા રોડ', 'pincode' => '394248', 'lat' => 20.9570, 'lng' => 73.1500],
                        ['name_en' => 'Karchelia', 'name_gu' => 'કરચેલિયા', 'pincode' => '394240', 'lat' => 20.9380, 'lng' => 73.1720],
                    ],
                ],
            ],
        ];

        $district = District::updateOrCreate(
            ['code' => $navsariData['code']],
            [
                'name_en' => $navsariData['name_en'],
                'name_gu' => $navsariData['name_gu'],
                'is_active' => true,
            ]
        );

        foreach ($navsariData['talukas'] as $tData) {
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

    public function down(): void
    {
        $district = District::where('code', 'NAVSARI')->first();
        if ($district) {
            $district->delete();
        }
    }
};
