<?php

use Malico\Teams\Contracts\InvitesTeamMembers;
use Malico\Teams\Contracts\RemovesTeamMembers;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use Malico\Teams\Teams;

new class extends Component {
    public $email = '';
    public $role = '';

    #[Computed]
    public function team()
    {
        return auth()->user()->currentTeam;
    }

    #[Computed]
    public function invitations()
    {
        return auth()->user()->currentTeam->invitations ?? collect();
    }

    #[Computed]
    public function members()
    {
        return auth()->user()->currentTeam->users ?? collect();
    }

    public function inviteTeamMember()
    {
        $user = auth()->user();
        $currentTeam = $user->currentTeam;

        if (!$currentTeam) {
            return;
        }

        app(InvitesTeamMembers::class)->invite($user, $currentTeam, $this->email, $this->role);

        $this->reset('email', 'role');
        $this->dispatch('member-invited');
    }

    public function removeTeamMember(User $member)
    {
        $currentTeam = auth()->user()->currentTeam;

        if (!$currentTeam) {
            return;
        }

        app(RemovesTeamMembers::class)->remove(auth()->user(), $currentTeam, $member);

        $this->dispatch('member-removed');
    }

    public function cancelInvitation($invitationId)
    {
        $currentTeam = auth()->user()->currentTeam;
        $invitation = $currentTeam->invitations()->find($invitationId);

        if (!$currentTeam || !$invitation) {
            return;
        }
        $invitation->delete();

        $this->dispatch('invitation-cancelled');
    }
};

?>

<section class="w-full">
    @include('partials.teams-heading')

    <x-teams.layout
        :heading="__('Team Members')"
        :subheading="__('Manage who has access to this team and their roles')"
        permission="members.view"
    >
        @if ($this->team)
            <div class="my-6 w-full space-y-6">
                <!-- Add Member Form -->
                <div class="bg-white rounded-lg p-6 shadow">
                    <flux:heading class="mb-4" size="sm">{{ __('Invite New Member') }}</flux:heading>

                    <form class="space-y-4" wire:submit="inviteTeamMember">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <flux:input
                                    :label="__('Email Address')"
                                    placeholder="{{ __('Enter email address') }}"
                                    required
                                    type="email"
                                    wire:model="email"
                                />
                            </div>
                            <div>
                                <flux:select
                                    :label="__('Role')"
                                    required
                                    wire:model="role"
                                >
                                    @foreach (Teams::getRoles() as $role)
                                        <option value="{{ $role->key }}">
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </flux:select>
                            </div>
                            <div class="">
                                <flux:button
                                    size="sm"
                                    type="submit"
                                    variant="primary"
                                >
                                    {{ __('Send Invite') }}
                                </flux:button>
                            </div>
                        </div>

                        <x-action-message class="mt-2" on="member-invited">
                            {{ __('Invitation sent successfully.') }}
                        </x-action-message>
                    </form>
                </div>

                <!-- Pending Invitations -->
                @if ($this->invitations->count() > 0)
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <flux:heading size="sm">{{ __('Pending Invitations') }}</flux:heading>
                            <flux:badge color="yellow">{{ $this->invitations->count() }} {{ __('pending') }}
                            </flux:badge>
                        </div>

                        <div class="space-y-3">
                            @foreach ($this->invitations as $invitation)
                                <div class="bg-white flex items-center justify-between rounded-lg border border-yellow-200 p-4 shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-yellow-100 text-yellow-600">
                                            <flux:icon class="size-5" icon="clock" />
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $invitation->email }}</div>
                                            <div class="text-zinc-600 text-sm">{{ __('Invited') }}
                                                {{ $invitation->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <flux:badge color="yellow">{{ $invitation->role?->name ?? 'Member' }}
                                        </flux:badge>
                                        <flux:badge color="yellow">{{ __('Pending') }}</flux:badge>
                                        <flux:button
                                            class="text-error hover:text-error"
                                            size="sm"
                                            variant="ghost"
                                            wire:click="cancelInvitation({{ $invitation->id }})"
                                            wire:confirm="{{ __('Are you sure you want to cancel this invitation?') }}"
                                        >
                                            {{ __('Cancel') }}
                                        </flux:button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <x-action-message class="mt-2" on="invitation-cancelled">
                            {{ __('Invitation cancelled successfully.') }}
                        </x-action-message>
                    </div>
                @endif

                <!-- Current Members -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <flux:heading size="sm">{{ __('Current Members') }}</flux:heading>
                        <flux:badge>{{ $this->members->count() }} {{ __('members') }}</flux:badge>
                    </div>

                    <div class="space-y-3">
                        @foreach ($this->members as $member)
                            <div class="bg-white flex items-center justify-between rounded-lg p-4 shadow-sm border">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-600 font-medium text-white">
                                        {{ $member->initials() }}
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $member->name }}</div>
                                        <div class="text-zinc-600 text-sm">{{ $member->email }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <flux:badge>{{ $member->pivot->role?->name ?? 'member' }}</flux:badge>
                                    @if ($member->id === $this->team->owner->id)
                                        <flux:badge color="blue">{{ __('Owner') }}</flux:badge>
                                    @else
                                        <flux:button
                                            class="text-error hover:text-error"
                                            size="sm"
                                            variant="ghost"
                                            wire:click="removeTeamMember({{ $member->id }})"
                                            wire:confirm="{{ __('Are you sure you want to remove this member?') }}"
                                        >
                                            {{ __('Remove') }}
                                        </flux:button>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <x-action-message class="mt-2" on="member-removed">
                            {{ __('Member removed successfully.') }}
                        </x-action-message>

                        @if (session('error'))
                            <div class="text-error mt-2 text-sm">{{ session('error') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="border-zinc-200 bg-white my-6 rounded-lg border p-6 text-center shadow-sm">
                <p class="text-zinc-600">{{ __('No team selected. Please select a team to manage members.') }}</p>
            </div>
        @endif
    </x-teams.layout>
</section>
