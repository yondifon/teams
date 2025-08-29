<div class="relative mb-6 w-full">
    <flux:heading size="lg" level="1">{{ auth()->user()->currentTeam?->name ?? __('Team Settings') }}</flux:heading>
    <flux:subheading size="base" class="mb-6">{{ __('Manage your team settings') }}</flux:subheading>
    <flux:separator variant="subtle" />
</div>
