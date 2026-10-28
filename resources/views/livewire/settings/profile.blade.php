<?php

use App\Models\Event;
use App\Models\User;
use App\Notifications\TestMailNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';

    /** One of "", "success", "warning" or "error". */
    public string $testMailStatus = '';

    public string $testMailMessage = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Send a test mail to the stored address of the current user.
     */
    public function sendTestMail(): void
    {
        $user = Auth::user();
        $rateLimitKey = 'test-mail:'.$user->id;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $this->testMailStatus = 'error';
            $this->testMailMessage = __('Too many test mails. Try again in :seconds seconds.', ['seconds' => RateLimiter::availableIn($rateLimitKey)]);

            return;
        }

        RateLimiter::hit($rateLimitKey);

        $mailer = (string) config('mail.default');

        try {
            $user->notify(new TestMailNotification);
        } catch (Throwable $exception) {
            Event::logError('mail', 'Test mail could not be sent', ['recipient' => $user->email, 'mailer' => $mailer, 'error' => $exception->getMessage()]);

            $this->testMailStatus = 'error';
            $this->testMailMessage = __('The test mail could not be sent: :error', ['error' => $exception->getMessage()]);

            return;
        }

        Event::logInfo('mail', 'Test mail sent', ['recipient' => $user->email, 'mailer' => $mailer]);

        $this->testMailStatus = 'success';
        $this->testMailMessage = __('Test mail handed to the ":mailer" mailer for :email.', ['mailer' => $mailer, 'email' => $user->email]);

        // The log and array mailers accept everything without delivering anything.
        if (in_array($mailer, ['log', 'array'], true)) {
            $this->testMailStatus = 'warning';
            $this->testMailMessage .= ' '.__('This mailer does not deliver mail, so nothing will arrive. Set MAIL_MAILER to send real mail.');
        }
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout heading="Profile" subheading="Update your name and email address">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" label="{{ __('Name') }}" type="text" name="name" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" label="{{ __('Email') }}" type="email" name="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <p class="mt-2 text-sm text-gray-800">
                            {{ __('Your email address is unverified.') }}

                            <button
                                wire:click.prevent="resendVerificationNotification"
                                class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif

                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <flux:button size="sm" icon="envelope" wire:click.prevent="sendTestMail">{{ __('Send test mail') }}</flux:button>
                    <flux:text size="sm" class="text-zinc-500">{{ __('Sends a test mail to your saved email address.') }}</flux:text>
                </div>

                @if ($testMailMessage !== '')
                    <flux:callout class="mt-2"
                        :variant="match ($testMailStatus) { 'success' => 'success', 'warning' => 'warning', default => 'danger' }"
                        :icon="match ($testMailStatus) { 'success' => 'check-circle', 'warning' => 'exclamation-triangle', default => 'x-circle' }"
                        :heading="$testMailMessage" />
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <section class="mt-10 space-y-6">
            <div class="relative mb-5">
                <flux:heading>{{ __('Region') }}</flux:heading>
                <flux:subheading>{{ __('Timezone setting is currently set per ISCPP instance') }}</flux:subheading>
            </div>
                <flux:input label="{{ __('Timezone') }}" value="{{ auth()->user()->timezone() }}" type="text" readonly />
        </section>
        
        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
