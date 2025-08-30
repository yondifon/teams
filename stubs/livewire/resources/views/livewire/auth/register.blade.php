<?php

use Malico\Teams\Contracts\AcceptsTeamInvitations;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?TeamInvitation $pendingInvitation = null;
    public bool $hasInvitation = false;

    public function mount(): void
    {
        $this->email = request('email', '');

        if (!session('pending_invitation')) {
            return;
        }

        $this->pendingInvitation = TeamInvitation::find(session('pending_invitation'));
        if (!$this->pendingInvitation) {
            return;
        }

        if (User::where('email', $this->pendingInvitation->email)->exists()) {
            $this->redirectRoute('login', ['email' => $this->pendingInvitation->email]);
            return;
        }

        if ($this->pendingInvitation && $this->pendingInvitation->email === session('invitation_email')) {
            $this->email = $this->pendingInvitation->email;
            $this->hasInvitation = true;
        }
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ];

        // If user has invitation, email must match and can't be changed
        if ($this->hasInvitation && $this->pendingInvitation) {
            $rules['email'] = [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($value !== $this->pendingInvitation->email) {
                        $fail(__('Email must match the invitation email: :email', ['email' => $this->pendingInvitation->email]));
                    }
                },
            ];
        }

        $validated = $this->validate($rules);
        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        // If user registered with invitation, automatically accept it
        if ($this->hasInvitation && $this->pendingInvitation) {
            try {
                app(AcceptsTeamInvitations::class)->accept($user, $this->pendingInvitation);

                // Clear invitation session data
                session()->forget(['pending_invitation', 'invitation_email']);

                session()->flash(
                    'message',
                    __('Welcome to the team! Your account has been created and you\'ve been added to :team.', [
                        'team' => $this->pendingInvitation->team->name,
                    ]),
                );

                $this->redirect(route('teams.show', $this->pendingInvitation->team), navigate: true);
                return;
            } catch (\Exception $e) {
                // If invitation acceptance fails, still redirect to dashboard but show message
                session()->flash('error', __('Your account was created successfully, but there was an issue accepting the team invitation. Please try again.'));
            }
        }

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    @if ($hasInvitation && $pendingInvitation)
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
            :readonly="$hasInvitation"
            :variant="$hasInvitation ? 'filled' : null"
            :description="$hasInvitation ? __('Email address from your team invitation') : null"
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
                {{ $hasInvitation ? __('Create account & join team') : __('Create account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Already have an account?') }}</span>
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</div>