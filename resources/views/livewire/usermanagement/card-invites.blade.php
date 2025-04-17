<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    public $name = '';
    public $email = '';
    public $invitationLink = '';
    public $showLink = false;
    public $pendingInvitations = [];

    public function mount()
    {
        $this->refreshPendingInvitations();
    }
    
    public function refreshPendingInvitations()
    {
        // Get users that have no password set yet or have not verified their email (pending invitations)
        $this->pendingInvitations = User::whereNull('email_verified_at')->get();
    }
    
    public function createInvitation()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ]);
        
        $token = Str::random(64);
        
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make(Str::random(32)), 
        ]);
        
        $this->regenerateLink($user->id);        
        $this->reset(['name', 'email']);
        $this->refreshPendingInvitations();
    }
    
    public function regenerateLink($userId)
    {
        $user = User::findOrFail($userId);
        $token = Str::random(64);
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => bcrypt($token),
                'created_at' => now()
            ]
        );
        
        $this->invitationLink = route('invitation.accept', ['token' => $token, 'email' => $user->email]);
        $this->showLink = true;
    }
    
    public function deleteInvitation($userId)
    {
        $user = User::findOrFail($userId);
        
        \DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        $user->delete();
        $this->refreshPendingInvitations();
        $this->showLink = false;
    }
}; ?>

<div>
    <x-card title="Create New User Invitation" subtitle="Send an invitation to a new user">
        <x-card-details-input label="Name" wire:model="name" />
        @error('name') <div class="mt-2 text-sm text-red-600">{{ $message }}</div> @enderror
        <x-card-details-input label="Email" wire:model="email" type="email" />
        @error('email') <div class="mt-2 text-sm text-red-600">{{ $message }}</div> @enderror
        <x-a-button wire:click="createInvitation">Create Invitation</x-a-button>
        
        @if($showLink)
            <x-subcard>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    The invitation link has been generated. Share it with the user to allow them to set their password.
                </p>
                <x-card-details-input label="Link" wire:model.defer="invitationLink" value="{{ $invitationLink }}" readonly />
                <x-a-button x-data="{}" @click="navigator.clipboard.writeText('{{ $invitationLink }}'); $dispatch('notify', { message: 'Link copied to clipboard!' })">
                    Copy
                </x-a-button>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    <strong>Note:</strong> The link will expire after 24 hours.
                </p>
            </x-subcard>
        @endif
    </x-card>
    
    <x-card title="Pending Invitations" subtitle="Manage pending user invitations" class="mt-4">
        @if(count($pendingInvitations) > 0)
            <x-table.table>
                <x-table.thead>
                    <tr>
                        <x-table.th>Name</x-table.th>
                        <x-table.th>Email</x-table.th>
                        <x-table.th>Created At</x-table.th>
                        <x-table.th>Actions</x-table.th>
                    </tr>
                </x-table.thead>
                <tbody>
                    @foreach($pendingInvitations as $invitation)
                        <x-table.tr>
                            <x-table.td>{{ $invitation->name }}</x-table.td>
                            <x-table.td>{{ $invitation->email }}</x-table.td>
                            <x-table.td>{{ $invitation->created_at->format('Y-m-d H:i') }}</x-table.td>
                            <x-table.td>
                                <div class="flex space-x-2">
                                    <x-a-button size="xs" wire:click="regenerateLink({{ $invitation->id }})">
                                        Regenerate Link
                                    </x-a-button>
                                    <x-a-button size="xs" variant="danger" wire:click="deleteInvitation({{ $invitation->id }})">
                                        Delete
                                    </x-a-button>
                                </div>
                            </x-table.td>
                        </x-table.tr>
                    @endforeach
                </tbody>
            </x-table.table>
        @else
            <p class="text-gray-600 dark:text-gray-400">No pending invitations.</p>
        @endif
    </x-card>
</div>