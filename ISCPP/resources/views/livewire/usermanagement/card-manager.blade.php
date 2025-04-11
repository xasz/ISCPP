<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;

new class extends Component {

    public $users;

    public function mount()
    {
        $this->users = User::all();
    }
}; ?>

<x-card title="User Manager" subtitle="Manage your users">
    <x-table.table>
        <x-table.thead>
            <tr>
                <x-table.th>Name</x-table.th>
                <x-table.th>Email</x-table.th>
                <x-table.th>Verified</x-table.th>
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
                            Verified
                        @else
                            Not Verified
                        @endif
                    </x-table.td>
                </x-table.tr>
            @endforeach
        </tbody>
    </x-table.table>
</x-card>