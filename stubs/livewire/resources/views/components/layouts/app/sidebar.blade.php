<flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    <livewire:team-switcher />

    <flux:brand href="#" logo="https://fluxui.dev/img/demo/logo.png" name="Acme Inc." class="px-2 dark:hidden" />
    <flux:brand href="#" logo="https://fluxui.dev/img/demo/logo-dark.png" name="Acme Inc." class="px-2 hidden dark:flex" />

    <flux:navlist variant="outline">
        <flux:navlist.group :heading="__('Platform')" class="grid">
            <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
        </flux:navlist.group>
    </flux:navlist>

    <flux:spacer />

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
