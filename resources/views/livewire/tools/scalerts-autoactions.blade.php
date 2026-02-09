
<?php

use Illuminate\Support\Facades\Http;
use Livewire\Volt\Component;

use App\Models\SCTenant;
use App\Services\HaloService;


use App\Models\SCAlertAutoAction;

new class extends Component {
    public array $autoActions = [];
    public string $newType = '';
    public string $newAction = '';
    public array $actions = ['SuppressWebhook', 'AutoAcknowledge'];

    public function mount()
    {
        $this->autoActions = SCAlertAutoAction::all()->map(function($row) {
            return [
                'id' => $row->id,
                'type' => $row->type,
                'action' => $row->action,
            ];
        })->toArray();
    }

    public string $errorMessage = '';

    public function addAutoAction()
    {
        $this->errorMessage = '';
        $type = trim($this->newType);
        $action = trim($this->newAction);
        if ($type === '' || $action === '') {
            $this->errorMessage = __('Type and Action are required.');
            return;
        }
        if (!in_array($action, $this->actions, true)) {
            $this->errorMessage = __('Invalid action selected.');
            return;
        }
        if (SCAlertAutoAction::where('type', $type)->where('action', $action)->exists()) {
            $this->errorMessage = __('This type/action combination already exists.');
            return;
        }
        $row = SCAlertAutoAction::create([
            'type' => $type,
            'action' => $action,
        ]);
        $this->autoActions[] = [
            'id' => $row->id,
            'type' => $row->type,
            'action' => $row->action,
        ];
        $this->newType = '';
        $this->newAction = '';
    }

    public function removeAutoAction($index)
    {
        $row = $this->autoActions[$index] ?? null;
        if ($row && isset($row['id'])) {
            SCAlertAutoAction::where('id', $row['id'])->delete();
        }
        unset($this->autoActions[$index]);
        $this->autoActions = array_values($this->autoActions);
    }
}; ?>


<x-card title="ISCPP SCAlert Auto-Action Settings">
    <x-table>
        <x-table.thead>
            <x-table.th>SCAlert Type</x-table.th>
            <x-table.th>Action</x-table.th>
            <x-table.th></x-table.th>
        </x-table.thead>
        <tbody>
            @foreach($autoActions as $idx => $row)
                <x-table.tr>
                    <x-table.td>{{ $row['type'] }}</x-table.td>
                    <x-table.td>{{ $row['action'] }}</x-table.td>
                    <x-table.td>
                        <x-a-button color="danger" wire:click="removeAutoAction({{ $idx }})">Delete</x-a-button>
                    </x-table.td>
                </x-table.tr>
            @endforeach
            <x-table.tr>
                <x-table.td>
                    <x-input type="text" wire:model.defer="newType" placeholder="Type..." class="w-full" />
                </x-table.td>
                <x-table.td>
                    <x-select wire:model.defer="newAction" class="w-full">
                        <option value="">Select Action</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}">{{ $action }}</option>
                        @endforeach
                    </x-select>
                </x-table.td>
                <x-table.td>
                    <x-a-button color="primary" wire:click="addAutoAction">Add</x-a-button>
                </x-table.td>
            </x-table.tr>
            @if($errorMessage)
                <x-table.tr>
                    <x-table.td colspan="3">
                        <span class="text-red-600">{{ $errorMessage }}</span>
                    </x-table.td>
                </x-table.tr>
            @endif
        </tbody>
    </x-table>
</x-card>

