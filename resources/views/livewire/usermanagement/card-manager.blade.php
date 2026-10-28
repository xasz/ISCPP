<?php

use App\Enums\UserRole;
use App\Models\Event;
use Livewire\Volt\Component;
use App\Models\User;

new class extends Component {
    /**
     * Runs on every request of this component, not only on mount.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-users');
    }


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

    public string $message = '';

    public function deleteUser($userId)
    {
        $user = User::find($userId);
        if (! $user) {
            return;
        }

        if ($user->is(auth()->user())) {
            $this->message = __('You cannot delete yourself.');

            return;
        }

        if ($user->isLastSuperAdmin()) {
            $this->message = __('The last superadmin cannot be deleted.');

            return;
        }

        $user->delete();
        $this->users = User::all();
        $this->closeModal();
    }

    public function changeRole(int $userId, string $role): void
    {
        $user = User::findOrFail($userId);
        $newRole = UserRole::tryFrom($role);

        if ($newRole === null || $user->role === $newRole) {
            return;
        }

        // Demoting yourself or the last superadmin would leave nobody able to manage users.
        if ($user->is(auth()->user())) {
            $this->message = __('You cannot change your own role.');
            $this->users = User::all();

            return;
        }

        if ($newRole !== UserRole::SuperAdmin && $user->isLastSuperAdmin()) {
            $this->message = __('The last superadmin cannot be demoted.');
            $this->users = User::all();

            return;
        }

        $user->update(['role' => $newRole]);
        Event::logInfo('usermanagement', 'User role changed', [
            'user' => $user->email,
            'role' => $newRole->value,
            'changedBy' => auth()->user()->email,
        ]);

        $this->message = __(':name is now :role.', ['name' => $user->name, 'role' => $newRole->label()]);
        $this->users = User::all();
    }
}; ?>

<x-card title="User Manager" subtitle="Manage your users">
    <x-table.table>
        <x-table.thead>
            <tr>
                <x-table.th>Name</x-table.th>
                <x-table.th>Email</x-table.th>
                <x-table.th>Role</x-table.th>
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
                        @if(auth()->user()->id === $user->id)
                            <flux:badge size="sm" :color="$user->role->badgeColor()">{{ $user->role->label() }}</flux:badge>
                        @else
                            <div class="w-40">
                                <flux:select size="sm" wire:key="role-{{ $user->id }}-{{ $user->role->value }}" wire:change="changeRole({{ $user->id }}, $event.target.value)">
                                    @foreach(App\Enums\UserRole::options() as $value => $label)
                                        <option value="{{ $value }}" @selected($user->role->value === $value)>{{ $label }}</option>
                                    @endforeach
                                </flux:select>
                            </div>
                        @endif
                    </x-table.td>
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
    @if($message !== '')
        <flux:text size="sm">{{ $message }}</flux:text>
    @endif
    <flux:text size="sm" class="text-zinc-500">{{ __('Superadmins can do everything. Editors can use everything except User Management and General Settings.') }}</flux:text>
    @if ($userDetails)
        <x-modal name="userDetails" title="User Details">
            <flux:text>Name: {{ $userDetails->name }}</flux:text>
            <flux:text>Email: {{ $userDetails->email }}</flux:text>
            <flux:text>Verified: {{ $userDetails->email_verified_at ? 'Yes' : 'No' }}</flux:text>
            <x-a-button size="xs" wire:click="deleteUser({{ $userDetails->id }})">Delete</x-a-button>
        </x-modal>
    @endif
</x-card>