<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $faviconUrl = \App\Domains\Settings\Branding::faviconUrl();
        @endphp
        <link rel="icon" href="{{ $faviconUrl ?: '/favicon.ico' }}" sizes="any">
        @if($faviconUrl)
            <link rel="icon" href="{{ $faviconUrl }}" type="image/png">
            <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
        @else
            <link rel="icon" href="/favicon.svg" type="image/svg+xml">
            <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        @endif

        @fonts

        @php
            $activeTheme = \App\Domains\Settings\Models\ThemeVersion::currentPublished();
            $light = $activeTheme?->light ?? [
                'primary' => '#B33925',
                'secondary' => '#2D5A3D',
                'accent' => '#D48822',
                'bg' => '#FDFBF7',
                'surface' => '#FFFFFF',
                'text' => '#1F2937',
                'text_muted' => '#6B7280',
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
            $dark = $activeTheme?->dark ?? [
                'primary' => '#E05A44',
                'secondary' => '#4E8B62',
                'accent' => '#E5A13B',
                'bg' => '#121212',
                'surface' => '#1E1E1E',
                'text' => '#F3F4F6',
                'text_muted' => '#9CA3AF',
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
        @endphp

        <style id="dynamic-theme-tokens">
            :root {
                --color-primary: {{ $light['primary'] }};
                --color-secondary: {{ $light['secondary'] }};
                --color-accent: {{ $light['accent'] }};
                --color-bg: {{ $light['bg'] }};
                --color-surface: {{ $light['surface'] }};
                --color-text: {{ $light['text'] }};
                --color-text-muted: {{ $light['text_muted'] }};
                --color-border: {{ $light['border'] }};
                --color-status-received: {{ $light['status_received'] }};
                --color-status-verification: {{ $light['status_verification'] }};
                --color-status-categorised: {{ $light['status_categorised'] }};
                --color-status-assigned: {{ $light['status_assigned'] }};
                --color-status-assistance: {{ $light['status_assistance'] }};
                --color-status-followup: {{ $light['status_followup'] }};
                --color-status-resolved: {{ $light['status_resolved'] }};
                --color-status-rejected: {{ $light['status_rejected'] }};
                --color-status-onhold: {{ $light['status_onhold'] }};
            }

            .dark {
                --color-primary: {{ $dark['primary'] }};
                --color-secondary: {{ $dark['secondary'] }};
                --color-accent: {{ $dark['accent'] }};
                --color-bg: {{ $dark['bg'] }};
                --color-surface: {{ $dark['surface'] }};
                --color-text: {{ $dark['text'] }};
                --color-text-muted: {{ $dark['text_muted'] }};
                --color-border: {{ $dark['border'] }};
                --color-status-received: {{ $dark['status_received'] }};
                --color-status-verification: {{ $dark['status_verification'] }};
                --color-status-categorised: {{ $dark['status_categorised'] }};
                --color-status-assigned: {{ $dark['status_assigned'] }};
                --color-status-assistance: {{ $dark['status_assistance'] }};
                --color-status-followup: {{ $dark['status_followup'] }};
                --color-status-resolved: {{ $dark['status_resolved'] }};
                --color-status-rejected: {{ $dark['status_rejected'] }};
                --color-status-onhold: {{ $dark['status_onhold'] }};
            }
        </style>

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Tribal Helping Hand') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased bg-thh-bg text-thh-text min-h-screen">
        <x-inertia::app />
    </body>
</html>
