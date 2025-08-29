<?php

use App\Actions\Teams\CreateTeam;
use function Livewire\Volt\{state};

state(['name' => '']);

$createTeam = function () {
    $this->validate([
        'name' => 'required|string|max:255',
    ]);

    $team = app(CreateTeam::class)->create(
        auth()->user(),
        ['name' => $this->name]
    );

    $this->redirect(route('teams.show', $team), navigate: true);
};

?>

<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">{{ __('Create Team') }}</h2>
            <p class="text-sm text-gray-600">{{ __('Create a new team to collaborate with others.') }}</p>
        </div>

        <form wire:submit="createTeam" class="p-6 space-y-6">
            <div>
                <flux:input 
                    wire:model="name" 
                    :label="__('Team Name')" 
                    type="text" 
                    placeholder="{{ __('Enter team name') }}"
                    required 
                    autofocus 
                />
            </div>

            <div class="flex items-center justify-end space-x-4">
                <flux:button variant="ghost" href="{{ route('dashboard') }}">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button type="submit" variant="primary">
                    {{ __('Create Team') }}
                </flux:button>
            </div>
        </form>
    </div>
</div>