<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Guard: only run if the settings table exists (safe during fresh migrations)
        if (!Schema::hasTable('settings')) {
            return;
        }

        $map = [
            'openai_api_key'         => 'services.openai.api_key',
            'gemini_api_key'         => 'services.gemini.api_key',
            'anthropic_api_key'      => 'services.claude.api_key',
            'tavily_api_key'         => 'services.tavily.api_key',
            'facebook_page_id'       => 'services.facebook.page_id',
            'facebook_access_token'  => 'services.facebook.access_token',
            'instagram_account_id'   => 'services.instagram.account_id',
            'instagram_access_token' => 'services.instagram.access_token',
            'linkedin_access_token'  => 'services.linkedin.access_token',
            'linkedin_person_id'     => 'services.linkedin.person_id',
        ];

        foreach ($map as $settingKey => $configKey) {
            $value = Setting::where('key', $settingKey)->value('value');
            if ($value) {
                Config::set($configKey, $value);
            }
        }
    }
}
