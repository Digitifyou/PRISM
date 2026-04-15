@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
    <p class="text-sm text-gray-500 mt-1">Configure your API keys and social media credentials. Values saved here override your .env file.</p>
</div>

<form action="{{ route('settings.update') }}" method="POST" class="space-y-8">
    @csrf

    {{-- AI Keys --}}
    @if(isset($settings['ai']))
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center gap-2 mb-6">
            <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
                <span class="text-indigo-600 text-sm">🤖</span>
            </div>
            <div>
                <h2 class="font-semibold text-gray-900">AI Providers</h2>
                <p class="text-xs text-gray-400">API keys for content generation and image creation.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($settings['ai'] as $setting)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ $setting->label }}</label>
                <input type="password" name="settings[{{ $setting->key }}]"
                       value="{{ $setting->value ?? '' }}"
                       placeholder="{{ $setting->value ? '••••••••••••••••' : 'Enter key...' }}"
                       autocomplete="off"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand font-mono">
            </div>
            @endforeach
        </div>

        <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
            <p class="text-xs text-amber-700">
                <strong>Tip:</strong> You need at least one AI key to generate content.
                <a href="https://platform.openai.com" target="_blank" class="underline">OpenAI</a> •
                <a href="https://ai.google.dev" target="_blank" class="underline">Gemini</a> •
                <a href="https://console.anthropic.com" target="_blank" class="underline">Claude</a>
            </p>
        </div>
    </div>
    @endif

    {{-- Social Media --}}
    @if(isset($settings['social']))
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center gap-2 mb-6">
            <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                <span class="text-blue-600 text-sm">📱</span>
            </div>
            <div>
                <h2 class="font-semibold text-gray-900">Social Media</h2>
                <p class="text-xs text-gray-400">Credentials for auto-publishing to your social accounts.</p>
            </div>
        </div>

        @php
        $socialGroups = [
            'Facebook'  => ['facebook_page_id', 'facebook_access_token'],
            'Instagram' => ['instagram_account_id', 'instagram_access_token'],
            'LinkedIn'  => ['linkedin_access_token', 'linkedin_person_id'],
        ];
        $settingsByKey = $settings['social']->keyBy('key');
        @endphp

        <div class="space-y-6">
            @foreach($socialGroups as $platformName => $keys)
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <span class="w-5 h-5 rounded text-xs font-bold flex items-center justify-center
                        {{ $platformName === 'Facebook' ? 'bg-blue-500 text-white' : ($platformName === 'Instagram' ? 'bg-pink-500 text-white' : 'bg-sky-500 text-white') }}">
                        {{ substr($platformName, 0, 1) }}
                    </span>
                    {{ $platformName }}
                </h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 pl-7">
                    @foreach($keys as $key)
                    @if(isset($settingsByKey[$key]))
                    @php $s = $settingsByKey[$key]; @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $s->label }}</label>
                        <input type="password" name="settings[{{ $s->key }}]"
                               value="{{ $s->value ?? '' }}"
                               placeholder="{{ $s->value ? '••••••••••••••••' : 'Enter value...' }}"
                               autocomplete="off"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand font-mono">
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-xs text-blue-700">
                <strong>Note:</strong> Images must be publicly accessible for Instagram and Facebook photo posts.
                On localhost, image publishing will fail — deploy to a public server for full functionality.
            </p>
        </div>
    </div>
    @endif

    <div class="flex items-center gap-4">
        <button type="submit"
                class="bg-brand hover:bg-indigo-600 text-white font-medium py-2 px-6 rounded-lg text-sm transition-colors">
            Save Settings
        </button>
        <p class="text-xs text-gray-400">Settings are saved to the database and take effect immediately.</p>
    </div>
</form>
@endsection
