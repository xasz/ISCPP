<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;
use PragmaRX\Google2FALaravel\Facade as Google2FA;
use PragmaRX\Google2FAQRCode\Google2FA as Google2FAQRCode;

new class extends Component {
    // User properties
    public $user;
    
    // 2FA properties
    public $showingQrCode = false;
    public $secret;
    public $qrCodeSvg;
    public $verificationCode = '';
    public $currentPassword = '';
    
    public function mount()
    {
        $this->user = Auth::user();
    }
    
    public function enableTwoFactor()
    {
        $this->validate([
            'currentPassword' => 'required|string',
        ]);
        
        // Verify current password
        if (!Hash::check($this->currentPassword, $this->user->password)) {
            throw ValidationException::withMessages([
                'currentPassword' => 'The provided password does not match our records.',
            ]);
        }
        
        // Generate new secret key
        $this->secret = Google2FA::generateSecretKey();
        
        // Generate QR code
        $google2fa = new Google2FAQRCode();
        $this->qrCodeSvg = $google2fa->getQRCodeInline(
            config('app.name'),
            $this->user->email,
            $this->secret
        );
        
        $this->showingQrCode = true;
        $this->currentPassword = '';
    }
    
    public function confirmTwoFactor()
    {
        $this->validate([
            'verificationCode' => 'required|string|size:6',
        ]);
        
        // Verify the provided code matches the secret
        $valid = Google2FA::verifyKey($this->secret, $this->verificationCode);
        
        if (!$valid) {
            $this->addError('verificationCode', 'The verification code is invalid.');
            return;
        }
        
        // Store the secret and enable 2FA
        $this->user->forceFill([
            'google2fa_secret' => encrypt($this->secret),
            'google2fa_enabled' => true,
        ])->save();
        
        $this->showingQrCode = false;
        $this->verificationCode = '';
        
        session()->flash('status', 'Two-factor authentication has been enabled.');
    }
    
    public function disableTwoFactor()
    {
        $this->validate([
            'currentPassword' => 'required|string',
        ]);
        
        // Verify current password
        if (!Hash::check($this->currentPassword, $this->user->password)) {
            throw ValidationException::withMessages([
                'currentPassword' => 'The provided password does not match our records.',
            ]);
        }
        
        // Disable 2FA
        $this->user->forceFill([
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
        ])->save();
        
        $this->currentPassword = '';
        
        session()->flash('status', 'Two-factor authentication has been disabled.');
    }
    
    public function cancelSetup()
    {
        $this->showingQrCode = false;
        $this->secret = null;
        $this->qrCodeSvg = null;
        $this->verificationCode = '';
        $this->currentPassword = '';
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')
    <x-settings.layout heading="Two-Factor Settings" subheading="Ensure your acccount security is top notch with two factor authentication">
        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ session('status') }}
            </div>
        @endif
        
        <!-- Current 2FA Status -->
        <div class="mb-6">
            
            <div class="flex items-center gap-2">
                @if ($user->google2fa_enabled)
                    <flux:badge>
                        {{ __('Enabled') }}
                    </flux:badge>
                    <flux:text>
                        {{ __('Two-factor authentication is currently enabled for your account.') }}
                    </flux:text>
                @else
                    <flux:badge>
                        {{ __('Disabled') }}
                    </flux:badge>
                    <flux:text>
                        {{ __('Two-factor authentication is currently disabled for your account.') }}
                    </flux:text>
                @endif
            </div>
        </div>
        
        <div class="mt-8">
            @if ($user->google2fa_enabled)
                <div x-data="{ showDisableForm: false }">
                    <x-a-button @click="showDisableForm = !showDisableForm">
                        {{ __('Disable Two-Factor Authentication') }}
                    </x-a-button>
                    <div x-show="showDisableForm" class="mt-4">
                        <form wire:submit.prevent="disableTwoFactor" class="space-y-4">
                            <div>
                                <x-card-details-input wire:model="currentPassword" label="{{__('Current Password')}}" type="password" />
                                @error('currentPassword')
                                    <span class="mt-2 text-sm text-red-600">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="flex items-center gap-4">
                                <x-a-button type="submit">
                                    {{ __('Disable') }}
                                </x-a-button>
                                <x-a-button @click="showDisableForm = false">
                                    {{ __('Cancel') }}
                                </x-a-button>
                            </div>
                        </form>
                    </div>
                </div>
            @elseif ($showingQrCode)
                <div class="mt-4">
                    <flux:heading>
                        {{ __('Set up your authenticator app') }}
                    </flux:heading>
                    
                    <div class="mb-6">
                        {!! $qrCodeSvg !!}
                    </div>
                    

                    <x-card-details-row label="{{ __('Secret Key') }}" value="{{ $secret }}" />
                    
                    <form wire:submit.prevent="confirmTwoFactor" class="mt-6 space-y-4">
                        <div>
                            <x-card-details-input wire:model="verificationCode" label="{{__('Verification Code')}}" type="text" inputmode="numeric" pattern="[0-9]*" />
                            @error('verificationCode')
                                <span class="mt-2 text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="flex items-center gap-4">
                            <x-a-button type="submit">
                                {{ __('Confirm') }}
                            </x-a-button>
                            <x-a-button type="button" wire:click="cancelSetup">
                                {{ __('Cancel') }}
                            </x-a-button>
                        </div>
                    </form>
                </div>
            @else
                <!-- Enable 2FA Form -->
                <div x-data="{ showEnableForm: false }">
                    <x-a-button @click="showEnableForm = !showEnableForm">
                        {{ __('Enable Two-Factor Authentication') }}
                    </x-a-button>
                    
                    <div x-show="showEnableForm" class="mt-4">
                        <form wire:submit.prevent="enableTwoFactor" class="space-y-4">
                            <div>
                                <x-card-details-input wire:model="currentPassword" label="{{__('Current Password')}}" type="password" />
                                @error('currentPassword')
                                    <span class="mt-2 text-sm text-red-600">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="flex items-center gap-4">
                                <x-a-button type="submit">
                                    {{ __('Continue') }}
                                </x-a-button>
                                <x-a-button type="button" @click="showEnableForm = false">
                                    {{ __('Cancel') }}
                                </x-a-button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </x-settings.layout>
</section>
