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
            <div class="my-8 w-full space-y-8">
                <!-- Add Member Form -->
                <div class="bg-white border border-zinc-200 rounded-xl p-8 shadow-sm">
                    <div class="mb-6">
                        <flux:heading class="text-zinc-900" size="sm">{{ __('Invite New Member') }}</flux:heading>
                        <p class="text-zinc-600 text-sm mt-1">{{ __('Send an invitation to add someone to your team') }}</p>
                    </div>

                    <form class="space-y-6" wire:submit="inviteTeamMember">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                        </div>
                        
                        <div class="flex items-center justify-between pt-2">
                            <flux:button
                                size="sm"
                                type="submit"
                                variant="primary"
                                class="px-6"
                            >
                                {{ __('Send Invite') }}
                            </flux:button>
                        </div>

                        <x-action-message class="mt-4" on="member-invited">
                            {{ __('Invitation sent successfully.') }}
                        </x-action-message>
                    </form>
                </div>

                <!-- Pending Invitations -->
                @if ($this->invitations->count() > 0)
                    <div class="space-y-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:heading size="sm" class="text-zinc-900">{{ __('Pending Invitations') }}</flux:heading>
                                <p class="text-zinc-600 text-sm mt-1">{{ __('Invitations waiting for acceptance') }}</p>
                            </div>
                            <flux:badge color="yellow" class="px-3 py-1 font-medium">
                                {{ $this->invitations->count() }} {{ __('pending') }}
                            </flux:badge>
                        </div>

                        <div class="bg-white border border-zinc-200 rounded-xl divide-y divide-zinc-100 shadow-sm">
                            @foreach ($this->invitations as $invitation)
                                <div class="p-6 hover:bg-zinc-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-50 border border-amber-200">
                                                <flux:icon class="size-6 text-amber-600" icon="clock" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-semibold text-zinc-900 truncate">{{ $invitation->email }}</div>
                                                <div class="text-zinc-500 text-sm mt-1">
                                                    {{ __('Invited') }} {{ $invitation->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-3 flex-shrink-0">
                                            <flux:badge color="zinc" variant="outline" class="hidden sm:inline-flex">
                                                {{ $invitation->role?->name ?? 'Member' }}
                                            </flux:badge>
                                            <flux:badge color="amber" class="px-3">
                                                {{ __('Pending') }}
                                            </flux:badge>
                                            <flux:button
                                                class="text-red-600 hover:text-red-700 hover:bg-red-50"
                                                size="sm"
                                                variant="ghost"
                                                wire:click="cancelInvitation({{ $invitation->id }})"
                                                wire:confirm="{{ __('Are you sure you want to cancel this invitation?') }}"
                                            >
                                                {{ __('Cancel') }}
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <x-action-message class="mt-4" on="invitation-cancelled">
                            {{ __('Invitation cancelled successfully.') }}
                        </x-action-message>
                    </div>
                @endif

                <!-- Current Members -->
                <div class="space-y-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <flux:heading size="sm" class="text-zinc-900">{{ __('Current Members') }}</flux:heading>
                            <p class="text-zinc-600 text-sm mt-1">{{ __('People who have access to this team') }}</p>
                        </div>
                        <flux:badge color="zinc" class="px-3 py-1 font-medium">
                            {{ $this->members->count() }} {{ __('members') }}
                        </flux:badge>
                    </div>

                    <div class="bg-white border border-zinc-200 rounded-xl divide-y divide-zinc-100 shadow-sm">
                        @foreach ($this->members as $member)
                            <div class="p-6 hover:bg-zinc-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 font-semibold text-zinc-700 border border-zinc-200">
                                            {{ $member->initials() }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-semibold text-zinc-900 truncate">{{ $member->name }}</div>
                                            <div class="text-zinc-500 text-sm mt-1 truncate">{{ $member->email }}</div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-3 flex-shrink-0">
                                        <flux:badge color="zinc" variant="outline" class="hidden sm:inline-flex">
                                            {{ $member->pivot->role?->name ?? 'member' }}
                                        </flux:badge>
                                        @if ($member->id === $this->team->owner->id)
                                            <flux:badge color="blue" class="px-3">
                                                {{ __('Owner') }}
                                            </flux:badge>
                                        @else
                                            <flux:button
                                                class="text-red-600 hover:text-red-700 hover:bg-red-50"
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
                            </div>
                        @endforeach
                    </div>

                    <x-action-message class="mt-4" on="member-removed">
                        {{ __('Member removed successfully.') }}
                    </x-action-message>

                </div>
            </div>
        @else
            <div class="border-zinc-200 bg-white my-6 rounded-lg border p-6 text-center shadow-sm">
                <p class="text-zinc-600">{{ __('No team selected. Please select a team to manage members.') }}</p>
            </div>
        @endif
    </x-teams.layout>
</section>
