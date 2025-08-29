<?php

use App\Actions\Teams\AcceptTeamInvitation;
use App\Actions\Teams\DeclineTeamInvitation;
use App\Models\TeamInvitation;
use function Livewire\Volt\{state, mount};

state(['invitation']);


$acceptInvitation = function () {
    if (!$this->invitation || !$this->invitation->team) {
        return $this->redirect('/');
    }

    app(AcceptTeamInvitation::class)->accept(
        auth()->user(),
        $this->invitation
    );

    session()->flash('message', __('Welcome to the team!'));
    $this->redirect(route('teams.show', $this->invitation->team), navigate: true);
};

$declineInvitation = function () {
    if (!$this->invitation) {
        return $this->redirect('/');
    }

    app(DeclineTeamInvitation::class)->decline(
        auth()->user(),
        $this->invitation
    );

    session()->flash('message', __('Invitation declined.'));
    $this->redirect('/', navigate: true);
};

?>

<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            {{ __('Team Invitation') }}
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            {{ __('You have been invited to join a team') }}
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            @if($invitation?->team)
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                        <flux:icon icon="user-group" class="h-6 w-6 text-blue-600" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">{{ $invitation->team->name }}</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ __('You have been invited to join this team as a :role', ['role' => $invitation->role ?? 'member']) }}
                    </p>
                </div>

                <div class="space-y-4">
                    <flux:error name="invitation" />
                    
                    <flux:button
                        wire:click="acceptInvitation"
                        variant="primary"
                        class="w-full"
                    >
                        {{ __('Accept Invitation') }}
                    </flux:button>

                    <flux:button
                        wire:click="declineInvitation"
                        variant="ghost"
                        class="w-full"
                    >
                        {{ __('Decline') }}
                    </flux:button>
                </div>
            @else
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                        <flux:icon icon="exclamation-triangle" class="h-6 w-6 text-red-600" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">{{ __('Invalid Invitation') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ __('This invitation is no longer valid or has expired.') }}
                    </p>

                    <flux:button href="/" variant="primary" class="mt-4">
                        {{ __('Go Home') }}
                    </flux:button>
                </div>
            @endif
        </div>
    </div>
</div>
