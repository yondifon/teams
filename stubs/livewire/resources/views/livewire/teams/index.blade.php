<?php

use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component {
    #[Computed]
    public function teams()
    {
        return auth()->user()->allTeams();
    }
};
?>

<div class="flex items-start max-md:flex-col">
    <div class="sticky top-0 me-10 w-full py-4 pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item href="{{ route('teams.create') }}" wire:navigate>{{ __('Create Team') }}</flux:navlist.item>
        </flux:navlist>
    </div>
    <div class="flex-1 self-stretch py-4 max-md:pt-6">
        <flux:heading>{{ __('Teams') }}</flux:heading>
        <flux:subheading>{{ __('Manage your teams and create new ones.') }}</flux:subheading>

        <flux:separator class="md:hidden" />

        <div class="mt-5 w-full">
            @if($this->teams->count() > 0)
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($this->teams as $team)
                        <div class="bg-white rounded-lg border shadow-sm p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ $team->name }}</h3>
                                @if($team->personal_team)
                                    <flux:badge color="blue">{{ __('Personal') }}</flux:badge>
                                @endif
                            </div>

                            <div class="text-sm text-gray-600 mb-4">
                                {{ __(':count members', ['count' => $team->users->count() + 1]) }}
                            </div>

                            <div class="flex gap-2">
                                <flux:button href="{{ route('teams.show', $team) }}" variant="ghost" size="sm">
                                    {{ __('View') }}
                                </flux:button>
                                <flux:button href="{{ route('teams.members', $team) }}" variant="ghost" size="sm">
                                    {{ __('Members') }}
                                </flux:button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
                        <flux:icon icon="user-group" class="h-12 w-12" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('No teams yet') }}</h3>
                    <p class="text-gray-600 mb-6">{{ __('Create your first team to get started.') }}</p>
                    <flux:button href="{{ route('teams.create') }}" variant="primary">
                        {{ __('Create Team') }}
                    </flux:button>
                </div>
            @endif
        </div>
    </div>
</div>
