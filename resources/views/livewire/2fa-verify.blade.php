<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use PragmaRX\Google2FALaravel\Facade as Google2FA;
use Livewire\Attributes\Layout;
new #[Layout('components.layouts.auth.simple')] class extends Component {
    public $code = '';
    
    public function verify()
    {
        $this->validate([
            'code' => 'required|string|size:6',
        ]);
        
        $user = Auth::user();
        
        if (!$user || !$user->google2fa_enabled) {
            return redirect()->intended(route('dashboard'));
        }
        
        // Decrypt the secret and verify the code
        $valid = Google2FA::verifyKey(
            decrypt($user->google2fa_secret),
            $this->code
        );
        
        if ($valid) {
            session(['google2fa_verified' => true]);
            return redirect()->intended(route('dashboard'));
        }
        
        $this->addError('code', 'The verification code is invalid.');
    }
}; ?>

<x-card title="Two-Factor Authentication" description="Enter the verification code from your authenticator app">
    <form wire:submit.prevent="verify" class="mt-6 space-y-6">
        <div>
            <div class="mt-2">
                <x-card-details-input
                    wire:model="code"
                    id="code"
                    label="{{ __('Authentication 6-digit code') }}"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="6"
                    autocomplete="one-time-code"
                    required
                    autofocus />
            </div>
            @error('code')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-a-button type="submit">
                {{ __('Verify') }}
            </x-a-button>
        </div>
        
        <div class="text-sm text-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-a-button type="submit">
                    {{ __('Sign out') }}
                </x-a-button>
            </form>
        </div>
    </form>
</x-card>
