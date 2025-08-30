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
    <flux:button key='{{ auth()->user()->currentTeam->name  }}' variant="ghost" size="sm" icon:trailing="chevron-up-down" class="flex items-center gap-2">
        <span class="max-w-[120px] truncate">{{ auth()->user()->currentTeam?->name ?? 'No Team' }}</span>
    </flux:button>

    <flux:menu>
        @forelse(auth()->user()->allTeams() as $team)
            <flux:menu.item
                :disabled="auth()->user()->current_team_id === $team->id"
                wire:click="switchTeam({{ $team->id }})"
                class="flex items-center justify-between {{ auth()->user()->currentTeam?->id === $team->id ? 'bg-subtle' : '' }}"
                :icon:trailing="auth()->user()->current_team_id === $team->id ? 'check' : null"
            >
                <span class="truncate">{{ $team->name }}</span>
            </flux:menu.item>
        @empty
            <flux:menu.item disabled>
                {{ __('No teams available') }}
            </flux:menu.item>
        @endforelse
    </flux:menu>
</flux:dropdown>
