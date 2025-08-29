<?php

use App\Actions\Teams\InviteTeamMember;
use App\Actions\Teams\RemoveTeamMember;
use App\Models\User;
use function Livewire\Volt\{state};

state(['email' => '', 'role' => '']);

$inviteTeamMember = function () {
    $user = auth()->user();
    $team = $user->currentTeam;

    if (! $team) {
        return;
    }

    app(InviteTeamMember::class)->invite(
        $user,
        $team,
        $this->email,
        $this->role
    );

    $this->reset('email', 'role');
    $this->dispatch('member-invited');
};

$removeTeamMember = function (User $member) {
    $team = auth()->user()->currentTeam;

    if (! $team) {
        return;
    }

    app(RemoveTeamMember::class)->remove(
        auth()->user(),
        $team,
        $member
    );

    $this->dispatch('member-removed');
};

$cancelInvitation = function ($invitationId) {
    $team = auth()->user()->currentTeam;
    $invitation = $team->invitations()->find($invitationId);

    if (! $team || ! $invitation) {
        return;
    }
    $invitation->delete();

    $this->dispatch('invitation-cancelled');
};

?>

<section class="w-full">
    @include('partials.teams-heading')

    <x-teams.layout
        :heading="__('Team Members')"
        :subheading="__('Manage who has access to this team and their roles')"
        permission="members.view"
    >
        @if(auth()->user()->currentTeam)
            <div class="my-6 w-full space-y-6">
                <!-- Add Member Form -->
                <div class="p-6 rounded-lg bg-surface">
                    <flux:heading size="sm" class="mb-4">{{ __('Invite New Member') }}</flux:heading>

                    <form wire:submit="inviteTeamMember" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                            <flux:input
                                wire:model="email"
                                :label="__('Email Address')"
                                type="email"
                                placeholder="{{ __('Enter email address') }}"
                                required
                            />
                            </div>
                            <div>
                            <flux:select required wire:model="role" :label="__('Role')">
                                @foreach (auth()->user()->currentTeam->roles as $role)
                                    <option value="{{ $role->code }}">
                                        {{ $role->code }}
                                    </option>
                                @endforeach
                            </flux:select>
                        </div>
                            <div class="">
                                <flux:button type="submit" variant="primary" size="sm">
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
                @if(auth()->user()->currentTeam->invitations->count() > 0)
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <flux:heading size="sm">{{ __('Pending Invitations') }}</flux:heading>
                            <flux:badge color="yellow">{{ auth()->user()->currentTeam->invitations->count() }} {{ __('pending') }}</flux:badge>
                        </div>

                        <div class="space-y-3">
                            @foreach(auth()->user()->currentTeam->invitations as $invitation)
                                <div class="flex items-center justify-between p-4 rounded-lg bg-surface border border-yellow-200">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
                                            <flux:icon icon="clock" class="size-5" />
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $invitation->email }}</div>
                                            <div class="text-sm text-secondary">{{ __('Invited') }} {{ $invitation->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <flux:badge color="yellow">{{ $invitation->role->code ?? 'Member' }}</flux:badge>
                                        <flux:badge color="yellow">{{ __('Pending') }}</flux:badge>
                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            wire:click="cancelInvitation({{ $invitation->id }})"
                                            wire:confirm="{{ __('Are you sure you want to cancel this invitation?') }}"
                                            class="text-error hover:text-error"
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
                        <flux:badge>{{ auth()->user()->currentTeam->users->count() }} {{ __('members') }}</flux:badge>
                    </div>

                    <div class="space-y-3">
                        @foreach(auth()->user()->currentTeam->users as $member)
                            <div class="flex items-center justify-between p-4 rounded-lg bg-surface">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-zinc-600 flex items-center justify-center text-white font-medium">
                                        {{ $member->initials() }}
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $member->name }}</div>
                                        <div class="text-sm text-secondary">{{ $member->email }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <flux:badge>{{ $member->pivot->role ?? 'member' }}</flux:badge>
                                    @if($member->id === auth()->user()->currentTeam->owner->id)
                                        <flux:badge color="blue">{{ __('Owner') }}</flux:badge>
                                    @else
                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            wire:click="removeTeamMember({{ $member->id }})"
                                            wire:confirm="{{ __('Are you sure you want to remove this member?') }}"
                                            class="text-error hover:text-error"
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

                        @if(session('error'))
                            <div class="text-sm text-error mt-2">{{ session('error') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="my-6 p-6 border border-default rounded-lg bg-surface text-center">
                <flux:text>{{ __('No team selected. Please select a team to manage members.') }}</flux:text>
            </div>
        @endif
    </x-teams.layout>
</section>
