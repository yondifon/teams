<?php

use Livewire\Volt\Component;
use App\Models\Team;

new class extends Component {
    public function switchTeam(Team $team)
    {
        auth()->user()->switchTeam($team);

        $this->redirect(request()->header('Referer') ?: route('dashboard'), navigate: true);
    }
}; ?>

<flux:dropdown align="end">
    <flux:button key='{{ auth()->user()->currentTeam?->name  }}' variant="ghost" size="sm" icon:trailing="chevron-up-down" class="flex w-full gap-2">
        {{ auth()->user()->currentTeam?->name ?? 'No Team' }}
    </flux:button>

    <flux:menu>
        @forelse(auth()->user()->allTeams() as $team)
            <flux:menu.item
                :disabled="auth()->user()->current_team_id === $team->id"
                wire:click="switchTeam({{ $team->id }})"
                type="button"
                class="flex items-center justify-between {{ auth()->user()->currentTeam?->id === $team->id ? 'bg-subtle' : '' }}"
                :icon:trailing="auth()->user()->current_team_id === $team->id ? 'check' : null"
            >
               {{ $team->name }}
            </flux:menu.item>

            @if($loop->last)
                <flux:menu.separator />
            @endif
        @empty
            <flux:menu.item disabled class="text-center py-3">
                {{ __('No teams yet') }}
            </flux:menu.item>
        @endforelse

        <flux:menu.item icon="plus" :href="route('teams.create')" wire:navigate>
            {{ __('Create Team') }}
        </flux:menu.item>
    </flux:menu>
</flux:dropdown>
