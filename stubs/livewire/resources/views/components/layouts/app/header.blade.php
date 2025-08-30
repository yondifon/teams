@props(['title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <flux:toast />
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <flux:brand href="#" logo="https://fluxui.dev/img/demo/logo.png" name="Acme Inc." class="px-2 dark:hidden" />
        <flux:brand href="#" logo="https://fluxui.dev/img/demo/logo-dark.png" name="Acme Inc." class="px-2 hidden dark:flex" />

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Platform')" class="grid">
                <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        <livewire:team-switcher />

        <flux:navlist variant="outline">
            <a href="https://github.com/livewire/flux" class="flex items-center gap-3 px-3 py-2 text-sm text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors">
                <flux:icon.bug-ant-slash variant="micro" />
                GitHub
            </a>

            <a href="https://fluxui.dev/docs" class="flex items-center gap-3 px-3 py-2 text-sm text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors">
                <flux:icon.document-text variant="micro" />
                Documentation
            </a>
        </flux:navlist>

        <flux:dropdown position="top" align="start" class="max-w-xs">
            <flux:profile avatar="https://fluxui.dev/img/demo/user.jpg" name="{{ auth()->user()->name }}" :email="auth()->user()->email" />

            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.radio checked>Online</flux:menu.radio>
                    <flux:menu.radio>Away</flux:menu.radio>
                    <flux:menu.radio>Busy</flux:menu.radio>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.item icon="cog-6-tooth">Settings</flux:menu.item>

                <flux:menu.separator />

                <flux:menu.item icon="arrow-right-start-on-rectangle" wire:click="$dispatch('logout')">Sign out</flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-3" />

        <flux:spacer />

        <flux:dropdown position="bottom" align="end" class="max-w-xs">
            <flux:profile avatar="https://fluxui.dev/img/demo/user.jpg" name="{{ auth()->user()->name }}" :email="auth()->user()->email" />

            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.radio checked>Online</flux:menu.radio>
                    <flux:menu.radio>Away</flux:menu.radio>
                    <flux:menu.radio>Busy</flux:menu.radio>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.item icon="cog-6-tooth">Settings</flux:menu.item>

                <flux:menu.separator />

                <flux:menu.item icon="arrow-right-start-on-rectangle" wire:click="$dispatch('logout')">Sign out</flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <flux:main>
        {{ $slot }}
    </flux:main>

    @livewire('notifications')
</body>
</html>
