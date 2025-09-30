<?php

use Malico\Teams\Contracts\UpdatesTeamNames;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Gate;

new class extends Component {
    public $name = '';

    public function mount()
    {
        $this->name = auth()->user()->currentTeam?->name ?? '';
    }

    public function updateTeamInformation()
    {
        $team = auth()->user()->currentTeam;

        if (!$team) {
            return;
        }

        Gate::authorize('update', $team);

        app(UpdatesTeamNames::class)->update(
            auth()->user(),
            $team,
            ['name' => $this->name]
        );

        $this->dispatch('team-updated', name: $team->name);
    }
};

?>

<section class="w-full">
    @include('partials.teams-heading')

    <x-teams.layout
        :heading="__('Team Settings')"
        :subheading="__('Manage your team information and preferences')"
        permission="team:update"
    >
        <form wire:submit="updateTeamInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Team Name')" type="text" required autofocus />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="team-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-teams.layout>
</section>
