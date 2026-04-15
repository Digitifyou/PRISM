<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $settings = [
            // AI API Keys
            ['key' => 'openai_api_key',        'label' => 'OpenAI API Key',          'group' => 'ai'],
            ['key' => 'gemini_api_key',         'label' => 'Google Gemini API Key',   'group' => 'ai'],
            ['key' => 'anthropic_api_key',      'label' => 'Anthropic (Claude) Key',  'group' => 'ai'],
            ['key' => 'tavily_api_key',         'label' => 'Tavily Research API Key', 'group' => 'ai'],
            // Facebook
            ['key' => 'facebook_page_id',       'label' => 'Facebook Page ID',        'group' => 'social'],
            ['key' => 'facebook_access_token',  'label' => 'Facebook Access Token',   'group' => 'social'],
            // Instagram
            ['key' => 'instagram_account_id',   'label' => 'Instagram Account ID',    'group' => 'social'],
            ['key' => 'instagram_access_token', 'label' => 'Instagram Access Token',  'group' => 'social'],
            // LinkedIn
            ['key' => 'linkedin_access_token',  'label' => 'LinkedIn Access Token',   'group' => 'social'],
            ['key' => 'linkedin_person_id',     'label' => 'LinkedIn Person ID',      'group' => 'social'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['label' => $setting['label'], 'group' => $setting['group'], 'value' => null]
            );
        }
    }
}
