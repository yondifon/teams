<?php

use Malico\Teams\Contracts\CreatesTeams;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';

    public function createTeam()
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        $team = app(CreatesTeams::class)->create(
            auth()->user(),
            ['name' => $this->name]
        );

        $this->redirect(route('teams.show', $team), navigate: true);
    }
};

?>

<section class="w-full">
    @include('partials.teams-heading')

    <x-teams.layout 
    :heading="__('Create Team')" 
    :subheading="__('Create a new team to collaborate with others.')"
>
    <form wire:submit="createTeam" class="space-y-6">
        <flux:input
            wire:model="name"
            :label="__('Team Name')"
            type="text"
            placeholder="{{ __('Enter team name') }}"
            required
            autofocus
        />

        <div class="flex items-center justify-end space-x-4">
            <flux:button variant="ghost" href="{{ route('dashboard') }}">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary">
                {{ __('Create Team') }}
            </flux:button>
        </div>
    </form>
</x-teams.layout>
</section>
