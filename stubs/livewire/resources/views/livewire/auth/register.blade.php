<?php

use App\Actions\Teams\AcceptTeamInvitation;
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
                app(AcceptTeamInvitation::class)->accept($user, $this->pendingInvitation);

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
        <x-auth-header :description="__('Create your account to join the team as :role', [
            'role' => $pendingInvitation->role ?? 'member',
        ])" :title="__('Join :team', ['team' => $pendingInvitation->team->name])" />
    @else
        <x-auth-header :description="__('Enter your details below to create your account')" :title="__('Create an account')" />
    @endif

    <!-- Session Status -->
    <x-auth-session-status :status="session('status')" class="text-center" />

    <form
        class="flex flex-col gap-6"
        method="POST"
        wire:submit="register"
    >
        <!-- Name -->
        <flux:input
            :label="__('Name')"
            :placeholder="__('Full name')"
            autocomplete="name"
            autofocus
            required
            type="text"
            wire:model="name"
        />

        <!-- Email Address -->
        <flux:input
            :description="$hasInvitation ? __('Email address from your team invitation') : null"
            :label="__('Email address')"
            :readonly="$hasInvitation"
            :variant="$hasInvitation ? 'filled' : null"
            autocomplete="email"
            placeholder="email@example.com"
            required
            type="email"
            wire:model="email"
        />

        <!-- Password -->
        <flux:input
            :label="__('Password')"
            :placeholder="__('Password')"
            autocomplete="new-password"
            required
            type="password"
            viewable
            wire:model="password"
        />

        <!-- Confirm Password -->
        <flux:input
            :label="__('Confirm password')"
            :placeholder="__('Confirm password')"
            autocomplete="new-password"
            required
            type="password"
            viewable
            wire:model="password_confirmation"
        />

        <div class="flex items-center justify-end">
            <flux:button
                class="w-full"
                type="submit"
                variant="primary"
            >
                {{ $hasInvitation ? __('Create account & join team') : __('Create account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 text-center text-sm text-zinc-600 rtl:space-x-reverse dark:text-zinc-400">
        <span>{{ __('Already have an account?') }}</span>
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</div>
