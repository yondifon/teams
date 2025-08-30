<?php

use Malico\Teams\Contracts\AcceptsTeamInvitations;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;
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

        if (!User::where('email', $this->pendingInvitation->email)->exists()) {
            $this->redirectRoute('register', ['email' => $this->pendingInvitation->email]);
            return;
        }

        if ($this->pendingInvitation && $this->pendingInvitation->email === session('invitation_email')) {
            $this->email = $this->pendingInvitation->email;
            $this->hasInvitation = true;
        }
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        // Check if user exists and redirect to register if they don't
        if (!User::where('email', $this->email)->exists()) {
            if ($this->hasInvitation) {
                $this->redirect(route('register', ['email' => $this->email]));
                return;
            }

            throw ValidationException::withMessages([
                'email' => __('No account found with this email address. Please create an account first.'),
            ]);
        }

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        // If user logged in with invitation, automatically accept it
        if ($this->hasInvitation && $this->pendingInvitation) {
            try {
                app(AcceptsTeamInvitations::class)->accept(auth()->user(), $this->pendingInvitation);

                // Clear invitation session data
                session()->forget(['pending_invitation', 'invitation_email']);

                session()->flash(
                    'message',
                    __('Welcome back! You\'ve been added to :team.', [
                        'team' => $this->pendingInvitation->team->name,
                    ]),
                );

                $this->redirect(route('teams.show', $this->pendingInvitation->team), navigate: true);
                return;
            } catch (\Exception $e) {
                // If invitation acceptance fails, still redirect to dashboard but show message
                session()->flash('error', __('You\'ve been logged in successfully, but there was an issue accepting the team invitation. Please try again.'));
            }
        }

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}; ?>

<div class="flex flex-col gap-6">
    @if ($hasInvitation && $pendingInvitation)
        <x-auth-header :title="__('Sign in to join :team', ['team' => $pendingInvitation->team->name])" :description="__('Enter your password to sign in and join the team')" />
    @else
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />
    @endif

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="login" class="flex flex-col gap-6">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@example.com"
            :readonly="$hasInvitation"
            :variant="$hasInvitation ? 'filled' : null"
            :description="$hasInvitation ? __('Email address from your team invitation') : null"
        />

        <!-- Password -->
        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Password')"
                viewable
            />

            @if (Route::has('password.request'))
                <flux:link class="absolute end-0 top-0 text-sm" :href="route('password.request')" wire:navigate>
                    {{ __('Forgot your password?') }}
                </flux:link>
            @endif
        </div>

        <!-- Remember Me -->
        <flux:checkbox wire:model="remember" :label="__('Remember me')" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">
                {{ $hasInvitation ? __('Sign in & join team') : __('Log in') }}
            </flux:button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Don\'t have an account?') }}</span>
            <flux:link :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:link>
        </div>
    @endif
</div>