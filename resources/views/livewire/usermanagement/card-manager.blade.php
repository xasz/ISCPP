<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component {

    public $users;

    public $userDetails;

    public function mount()
    {
        $this->users = User::all();
    }

    
    public function showDetails($userId)
    {
        $this->userDetails = User::find($userId);
    }    
    
    public function closeModal()
    {
        $this->userDetails = null;
    }

    public function deleteUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $user->delete();
            $this->users = User::all();
            $this->closeModal();
        }
    }
}; ?>

<x-card title="User Manager" subtitle="Manage your users">
    <x-table.table>
        <x-table.thead>
            <tr>
                <x-table.th>Name</x-table.th>
                <x-table.th>Email</x-table.th>
                <x-table.th>Verified</x-table.th>
                <x-table.th>MFA</x-table.th>
                <x-table.th>Actions</x-table.th>
            </tr>
        </x-table.thead>
        <tbody>
            @foreach($users as $user)
                <x-table.tr>
                    <x-table.td>
                        {{ $user->name }}
                    </x-table.td>
                    <x-table.td>{{ $user->email }}</x-table.td>
                    <x-table.td>
                        
                        @if($user->email_verified_at)
                            <flux:badge color="green">Verified</flux:badge>
                        @else
                            <flux:badge color="red">Not Verified</flux:badge>
                        @endif
                    </x-table.td>
                    <x-table.td>
                        @if($user->google2fa_enabled)
                            <flux:badge color="green">Enabled</flux:badge>
                        @else
                            <flux:badge color="red">Disabled</flux:badge>
                        @endif
                    </x-table.td>
                    <x-table.td>
                        @if(auth()->user()->id !== $user->id)
                        <x-a-button size="xs" wire:click="showDetails({{ $user->id }})">Details</x-a-button>
                        @endif
                    </x-table.td>
                </x-table.tr>
            @endforeach
        </tbody>
    </x-table.table>
    @if ($userDetails)
        <x-modal name="userDetails" title="User Details">
            <flux:text>Name: {{ $userDetails->name }}</flux:text>
            <flux:text>Email: {{ $userDetails->email }}</flux:text>
            <flux:text>Verified: {{ $userDetails->email_verified_at ? 'Yes' : 'No' }}</flux:text>
            <x-a-button size="xs" wire:click="deleteUser({{ $userDetails->id }})">Delete</x-a-button>
        </x-modal>
    @endif
</x-card>