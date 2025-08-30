<?php

use Malico\Teams\Contracts\AcceptsTeamInvitations;
use Malico\Teams\Contracts\DeclinesTeamInvitations;
use App\Models\TeamInvitation;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public TeamInvitation $invitation;

    public function acceptInvitation()
    {
        app(AcceptsTeamInvitations::class)->accept(
            auth()->user(),
            $this->invitation
        );

        $this->redirect(route('teams.show', $this->invitation->team), navigate: true);
    }

    public function declineInvitation()
    {
        app(DeclinesTeamInvitations::class)->decline(
            auth()->user(),
            $this->invitation
        );

        $this->redirect('/', navigate: true);
    }

    public function switchToCorrectAccount()
    {
        auth()->logout();
        return $this->redirect(URL::signedRoute('login', [
            'invitation' => $this->invitation->id
        ]));
    }

    #[Computed]
    public function wrongUserLoggedIn()
    {
        return auth()->user()->email !== $this->invitation->email;
    }
};

?>

<div class="flex flex-col gap-6">
    @if($invitation?->team)
        @if($this->wrongUserLoggedIn)
            <flux:callout>
                <flux:callout.heading>{{ __('Wrong account') }}</flux:callout.heading>
                <flux:callout.text>{{ __('This invitation was sent to :email, but you\'re signed in as :current. Please switch accounts to accept this invitation.', ['email' => $invitation->email, 'current' => auth()->user()->email]) }}</flux:callout.text>

                <x-slot name="actions">
                    <flux:button
                        wire:click="switchToCorrectAccount"
                        size="sm"
                    >
                        {{ __('Switch to :email', ['email' => $invitation->email]) }}
                    </flux:button>
                    <flux:button
                        href="/"
                        variant="ghost"
                        size="sm"
                    >
                        {{ __('Go Home') }}
                    </flux:button>
                </x-slot>
            </flux:callout>
        @else
            <x-auth-header
                :title="__('Join :team', ['team' => $invitation->team->name])"
                :description="__('You have been invited to join this team as :role', ['role' => $invitation->role?->name ?? 'member'])"
            />
            <flux:error name="invitation" />
            <div class="flex flex-col gap-4">
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
        @endif
    @else
        <x-auth-header
            :title="__('Invalid Invitation')"
            :description="__('This invitation is no longer valid or has expired.')"
        />

        <flux:button href="/" variant="primary" class="w-full">
            {{ __('Go Home') }}
        </flux:button>
    @endif
</div>
