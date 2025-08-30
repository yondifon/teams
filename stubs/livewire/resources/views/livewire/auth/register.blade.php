<?php

use Malico\Teams\Contracts\AcceptsTeamInvitations;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public ?TeamInvitation $pendingInvitation = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $invitationId = request('invitation');
        if (!$invitationId) {
            $this->email = request('email', '');
            return;
        }

        if (!URL::hasValidSignature(request())) {
            abort(403, 'Invalid invitation link.');
        }

        $this->pendingInvitation = TeamInvitation::find($invitationId);
        if (!$this->pendingInvitation) {
            return;
        }

        $this->email = $this->pendingInvitation->email;

        if (User::where('email', $this->pendingInvitation->email)->exists()) {
            $this->redirectRoute('login', [
                'invitation' => $invitationId
            ], navigate: true);
            return;
        }
    }

    private function extraEmailRule()
    {
        if ($this->pendingInvitation) {
            return function ($attribute, $value, $fail) {
                if ($value !== $this->pendingInvitation->email) {
                    $fail(__('Email must match the invitation email: :email', ['email' => $this->pendingInvitation->email]));
                }
            };
        }

        return 'unique:' . User::class;
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', $this->extraEmailRule()],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);
        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        // If user registered with invitation, automatically accept it
        if ($this->pendingInvitation) {
            app(AcceptsTeamInvitations::class)->accept($user, $this->pendingInvitation);

            $this->redirect(route('teams.show', $this->pendingInvitation->team), navigate: true);
            return;
        }

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    @if ($pendingInvitation)
        <x-auth-header :title="__('Join :team', ['team' => $pendingInvitation->team->name])" :description="__('Create your account to join the team as :role', [
            'role' => $pendingInvitation->role?->name ?? 'member',
        ])" />
    @else
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />
    @endif

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <flux:input
            wire:model="name"
            :label="__('Name')"
            type="text"
            required
            autofocus
            autocomplete="name"
            :placeholder="__('Full name')"
        />

        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autocomplete="email"
            placeholder="email@example.com"
            :readonly="$pendingInvitation"
            :variant="$pendingInvitation ? 'filled' : null"
            :description="$pendingInvitation ? __('Email address from your team invitation') : null"
        />

        <!-- Password -->
        <flux:input
            wire:model="password"
            :label="__('Password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Password')"
            viewable
        />

        <!-- Confirm Password -->
        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirm password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Confirm password')"
            viewable
        />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ $pendingInvitation ? __('Create account & join team') : __('Create account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Already have an account?') }}</span>
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</div>
