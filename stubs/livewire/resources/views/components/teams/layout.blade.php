@props([
    'heading' => null,
    'subheading' => null,
])

<div class="flex items-start max-md:flex-col">
    <div class="sticky top-0 me-10 w-full py-4 pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('teams.show')" wire:navigate>{{ __('Team') }}</flux:navlist.item>
            <flux:navlist.item :href="route('teams.members')" wire:navigate>{{ __('Members') }}</flux:navlist.item>
        </flux:navlist>
    </div>
    <div class="flex-1 self-stretch py-4 max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <flux:separator class="md:hidden" />

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
