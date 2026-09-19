<?php

use App\Domains\Content\Models\Category;
use App\Domains\Settings\Models\HomeTile;
use App\Domains\Settings\Models\Language;
use App\Domains\Settings\Models\ThemeVersion;
use App\Domains\Settings\Models\Translation;
use App\Domains\Users\Models\District;

test('config endpoints return valid theme, languages, and translation dictionary', function () {
    ThemeVersion::create([
        'version' => 1,
        'light' => ['primary' => '#B45309', 'background' => '#FDFBF7'],
        'dark' => ['primary' => '#F59E0B', 'background' => '#0C0A09'],
        'meta' => ['appName' => 'Tribal Helping Hand'],
        'is_published' => true,
        'published_at' => now(),
    ]);

    Language::create([
        'code' => 'gu',
        'name' => 'Gujarati',
        'native_name' => 'ગુજરાતી',
        'is_default' => true,
        'is_enabled' => true,
    ]);

    Translation::create([
        'group' => 'app',
        'key' => 'title',
        'locale' => 'gu',
        'value' => 'આદિવાસી સહાયક હાથ',
    ]);

    HomeTile::create([
        'key' => 'schemes',
        'icon' => 'file-text',
        'target_route' => 'schemes',
        'sort_order' => 1,
        'is_enabled' => true,
    ]);

    District::create([
        'name_en' => 'Navsari',
        'name_gu' => 'નવસારી',
        'code' => 'NAV',
    ]);

    Category::create([
        'slug' => 'health',
        'icon' => 'heart',
    ]);

    // 1. Theme
    $themeRes = $this->getJson('/api/v1/config/theme');
    $themeRes->assertOk()
        ->assertJsonPath('data.version', 1)
        ->assertJsonPath('data.light.primary', '#B45309');

    // 2. Languages
    $langRes = $this->getJson('/api/v1/config/languages');
    $langRes->assertOk()
        ->assertJsonFragment(['code' => 'gu', 'name' => 'Gujarati']);

    // 3. Translations
    $transRes = $this->getJson('/api/v1/config/translations?locale=gu');
    $transRes->assertOk();
    expect($transRes->json('data.translations')['app.title'])->toBe('આદિવાસી સહાયક હાથ');

    // 4. Home Tiles
    $tilesRes = $this->getJson('/api/v1/config/home-tiles');
    $tilesRes->assertOk()
        ->assertJsonFragment(['key' => 'schemes']);

    // 5. Master Data
    $masterRes = $this->getJson('/api/v1/config/master-data');
    $masterRes->assertOk()
        ->assertJsonFragment(['name_en' => 'Navsari'])
        ->assertJsonFragment(['slug' => 'health']);
});
