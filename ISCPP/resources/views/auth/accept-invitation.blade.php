<x-layouts.auth.simple>
    <x-card title="Welcome {{ $name }}!" subtitle="You've been invited to join ISCPP. Please set your password to complete the registration process.">
        <form method="POST" action="{{ route('invitation.process') }}" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <!-- Password -->
            <x-card-details-input 
                label="Password" 
                type="password" 
                id="password" 
                name="password" 
                required
            />
            @error('password')
                <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
            @enderror

            <!-- Confirm Password -->
            <x-card-details-input 
                label="Confirm Password" 
                type="password" 
                id="password_confirmation" 
                name="password_confirmation" 
                required
            />
            @error('password_confirmation')
                <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
            @enderror

            <div class="flex justify-end">
                <x-a-button type="submit">
                    {{ __('Set Password') }}
                </x-a-button>
            </div>
        </form>
    </x-card>
</x-layouts.auth.simple>