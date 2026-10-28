<?php

use App\Models\ISCPPEventLog;
use App\Models\ISCPPEventRule;
use App\Models\ISCPPEventRuleCondition;
use App\Models\SCTenant;
use App\Models\SCTenantGroup;
use App\Services\ISCPPEventRegistry;
use App\Services\ISCPPWebhookSender;
use Livewire\Volt\Component;

new class extends Component {
    public ?int $ruleId = null;

    public string $name = '';

    public bool $enabled = true;

    public string $eventKey = '';

    /** One of "all", "tenant" or "group". */
    public string $tenantScope = 'all';

    public string $tenantId = '';

    public string $tenantGroupId = '';

    /** @var array<int, array{path: string, operator: string, value: string}> */
    public array $conditions = [];

    public string $url = '';

    public string $basicAuthUsername = '';

    public string $basicAuthPassword = '';

    public bool $hasStoredPassword = false;

    public bool $autoDisabled = false;

    public int $consecutiveFailures = 0;

    public function mount(?int $ruleId = null): void
    {
        $this->ruleId = $ruleId;
        $this->eventKey = (string) collect(app(ISCPPEventRegistry::class)->keys())->first();

        if ($ruleId === null) {
            return;
        }

        $rule = ISCPPEventRule::with('conditions')->findOrFail($ruleId);

        $this->name = $rule->name;
        $this->enabled = $rule->enabled;
        $this->eventKey = $rule->event_key;
        $this->tenantId = (string) $rule->tenantId;
        $this->tenantGroupId = (string) $rule->sc_tenant_group_id;
        $this->tenantScope = match (true) {
            $rule->tenantId !== null => 'tenant',
            $rule->sc_tenant_group_id !== null => 'group',
            default => 'all',
        };
        $this->url = $rule->url;
        $this->basicAuthUsername = (string) $rule->basic_auth_username;
        $this->hasStoredPassword = filled($rule->basic_auth_password);
        $this->autoDisabled = $rule->wasAutoDisabled();
        $this->consecutiveFailures = $rule->consecutive_failures;

        $this->conditions = $rule->conditions->map(fn (ISCPPEventRuleCondition $condition) => [
            'path' => $condition->path,
            'operator' => $condition->operator,
            'value' => collect((array) $condition->value)->implode(', '),
        ])->all();
    }

    public function with(): array
    {
        $registry = app(ISCPPEventRegistry::class);

        return [
            'eventOptions' => $registry->options(),
            'operatorOptions' => ISCPPEventRuleCondition::operatorOptions(),
            'sctenants' => SCTenant::orderBy('name')->get(['id', 'name']),
            'sctenantGroups' => SCTenantGroup::orderBy('name')->withCount('tenants')->get(['id', 'name']),
            'suggestedPaths' => ['event.severity' => __('Event severity')] + $registry->filterablePathsFor($this->eventKey),
            'examplePayload' => $registry->examplePayloadFor($this->eventKey),
            'preview' => $this->preview(),
        ];
    }

    /**
     * How many of the most recent recorded events this rule would match.
     *
     * Makes a rule predictable before it is saved, and turns a typo in a condition path
     * from a silent permanent no-match into something visible.
     *
     * @return array{total: int, matched: int}
     */
    private function preview(): array
    {
        $recent = ISCPPEventLog::query()->orderByDesc('id')->limit(100)->get();

        if ($recent->isEmpty()) {
            return ['total' => 0, 'matched' => 0];
        }

        $rule = new ISCPPEventRule([
            'name' => $this->name,
            'enabled' => true,
            'event_key' => $this->eventKey,
            ...$this->scopeAttributes(),
        ]);
        $rule->enabled = true;
        $rule->setRelation('SCTenantGroup', $rule->sc_tenant_group_id
            ? SCTenantGroup::with('tenants:id')->find($rule->sc_tenant_group_id)
            : null);

        $rule->setRelation('conditions', collect($this->conditions)
            ->filter(fn (array $condition) => filled($condition['path'] ?? ''))
            ->map(fn (array $condition) => new ISCPPEventRuleCondition([
                'path' => $condition['path'],
                'operator' => $condition['operator'] ?? 'eq',
                'value' => $this->splitValue((string) ($condition['value'] ?? '')),
            ])));

        return [
            'total' => $recent->count(),
            'matched' => $recent->filter(fn (ISCPPEventLog $event) => $rule->matches($event))->count(),
        ];
    }

    /**
     * Only the column of the chosen scope is set, so a rule never carries both.
     *
     * @return array{tenantId: ?string, sc_tenant_group_id: ?int}
     */
    private function scopeAttributes(): array
    {
        return [
            'tenantId' => $this->tenantScope === 'tenant' && $this->tenantId !== '' ? $this->tenantId : null,
            'sc_tenant_group_id' => $this->tenantScope === 'group' && $this->tenantGroupId !== '' ? (int) $this->tenantGroupId : null,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function splitValue(string $value): array
    {
        return collect(explode(',', $value))->map(fn (string $item) => trim($item))->filter()->values()->all();
    }

    public function addCondition(): void
    {
        $this->conditions[] = ['path' => '', 'operator' => 'eq', 'value' => ''];
    }

    public function removeCondition(int $index): void
    {
        unset($this->conditions[$index]);
        $this->conditions = array_values($this->conditions);
    }

    public function save(ISCPPWebhookSender $sender)
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'eventKey' => 'required|string|in:'.implode(',', app(ISCPPEventRegistry::class)->keys()),
            'tenantScope' => 'required|in:all,tenant,group',
            'tenantId' => 'exclude_unless:tenantScope,tenant|required|string|exists:sc_tenants,id',
            'tenantGroupId' => 'exclude_unless:tenantScope,group|required|integer|exists:sc_tenant_groups,id',
            'url' => 'required|string|max:2048',
        ]);

        // Checked on save as well as at send time, so a bad URL is reported immediately
        // rather than discovered in a failed delivery.
        if (($reason = $sender->urlRejectionReason($this->url)) !== null) {
            $this->addError('url', $reason);

            return null;
        }

        $rule = $this->ruleId ? ISCPPEventRule::findOrFail($this->ruleId) : new ISCPPEventRule;

        $rule->fill([
            'name' => $this->name,
            'enabled' => $this->enabled,
            'event_key' => $this->eventKey,
            ...$this->scopeAttributes(),
            'url' => $this->url,
            'basic_auth_username' => $this->basicAuthUsername ?: null,
        ]);

        // Blank means "keep what is stored", so a password is never wiped by an edit.
        if (filled($this->basicAuthPassword)) {
            $rule->basic_auth_password = $this->basicAuthPassword;
        }

        // Enabling by hand also clears the circuit breaker.
        if ($this->enabled) {
            $rule->consecutive_failures = 0;
            $rule->disabled_at = null;
        }

        $rule->save();

        $rule->conditions()->delete();
        foreach ($this->conditions as $condition) {
            if (blank($condition['path'] ?? '')) {
                continue;
            }

            $rule->conditions()->create([
                'path' => $condition['path'],
                'operator' => $condition['operator'],
                'value' => $this->splitValue((string) ($condition['value'] ?? '')),
            ]);
        }

        ISCPPEventRule::flushCache();

        return $this->redirect(route('iscpp.rules.index'), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-4">
    <x-card :title="$ruleId ? __('Edit Rule') : __('Add Rule')"
            subtitle="{{ __('One event, one tenant scope, one webhook') }}">

        <x-card-details-input label="{{ __('Name') }}" wire:model="name" />
        @error('name') <flux:text class="text-red-600">{{ $message }}</flux:text> @enderror

        <div class="mt-4">
            <x-card-details-switch label="{{ __('Rule enabled') }}" wire:model.live="enabled" />
        </div>
    </x-card>

    <x-card title="{{ __('1. Event') }}">
        <div class="w-full max-w-md">
            <flux:select wire:model.live="eventKey" label="{{ __('Event') }}">
                @foreach($eventOptions as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </flux:select>
        </div>
        @error('eventKey') <flux:text class="text-red-600">{{ $message }}</flux:text> @enderror

        <flux:text size="sm" class="text-zinc-500">{{ $eventKey }}</flux:text>

        <flux:text size="sm" class="mt-2 text-zinc-500">{{ __('Example payload sent to the webhook:') }}</flux:text>
        <x-card-details-json :arr="$examplePayload" />
    </x-card>

    <x-card title="{{ __('2. Tenant') }}">
        <div class="w-full max-w-md">
            <flux:select wire:model.live="tenantScope" label="{{ __('Scope') }}">
                <option value="all">{{ __('All tenants') }}</option>
                <option value="tenant">{{ __('Single tenant') }}</option>
                <option value="group">{{ __('Tenant group') }}</option>
            </flux:select>
        </div>
        @error('tenantScope') <flux:text class="text-red-600">{{ $message }}</flux:text> @enderror

        @if($tenantScope === 'tenant')
            <div class="w-full max-w-md">
                <flux:select wire:model.live="tenantId" label="{{ __('Tenant') }}">
                    <option value="">{{ __('Select a tenant') }}</option>
                    @foreach($sctenants as $sctenant)
                        <option value="{{ $sctenant->id }}">{{ $sctenant->name }}</option>
                    @endforeach
                </flux:select>
            </div>
            @error('tenantId') <flux:text class="text-red-600">{{ $message }}</flux:text> @enderror
        @elseif($tenantScope === 'group')
            <div class="w-full max-w-md">
                <flux:select wire:model.live="tenantGroupId" label="{{ __('Tenant group') }}">
                    <option value="">{{ __('Select a group') }}</option>
                    @foreach($sctenantGroups as $sctenantGroup)
                        <option value="{{ $sctenantGroup->id }}">{{ $sctenantGroup->name }} ({{ $sctenantGroup->tenants_count }})</option>
                    @endforeach
                </flux:select>
            </div>
            @error('tenantGroupId') <flux:text class="text-red-600">{{ $message }}</flux:text> @enderror
            <flux:text size="sm" class="text-zinc-500">
                {{ __('Group membership is read when an event is recorded, so tenants added to the group later are covered automatically.') }}
                <flux:link href="{{ route('sctenantgroups.index') }}" wire:navigate>{{ __('Manage groups') }}</flux:link>
            </flux:text>
        @endif

        <flux:text size="sm" class="text-zinc-500">
            {{ __('All tenants also covers events that belong to no tenant. Tenants excluded from ISCPP never raise events.') }}
        </flux:text>
    </x-card>

    <x-card title="{{ __('3. Conditions (optional)') }}"
            subtitle="{{ __('All conditions must match. A path missing from the event never matches. For OR, create a second rule with the same event.') }}">

        @forelse($conditions as $index => $condition)
            <div class="flex flex-wrap items-end gap-2">
                <div class="w-64">
                    <flux:input wire:model.live="conditions.{{ $index }}.path" label="{{ __('Path') }}" list="iscpp-paths" size="sm" />
                </div>
                <div class="w-48">
                    <flux:select wire:model.live="conditions.{{ $index }}.operator" label="{{ __('Operator') }}" size="sm">
                        @foreach($operatorOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="w-56">
                    <flux:input wire:model.live="conditions.{{ $index }}.value" label="{{ __('Value (comma separated)') }}" size="sm" />
                </div>
                <flux:button size="xs" variant="danger" wire:click="removeCondition({{ $index }})">{{ __('Remove') }}</flux:button>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-500">{{ __('No conditions: every matching event is forwarded.') }}</flux:text>
        @endforelse

        <datalist id="iscpp-paths">
            @foreach($suggestedPaths as $path => $label)
                <option value="{{ $path }}">{{ $label }}</option>
            @endforeach
        </datalist>

        <div class="mt-3">
            <flux:button size="xs" icon="plus" wire:click="addCondition">{{ __('Add condition') }}</flux:button>
        </div>
    </x-card>

    <x-card title="{{ __('4. Webhook') }}" subtitle="{{ __('Where matching events are delivered') }}">
        @if($autoDisabled)
            <flux:callout icon="exclamation-triangle">
                <flux:callout.text>
                    {{ __('This rule was disabled automatically after') }} {{ $consecutiveFailures }}
                    {{ __('consecutive failed deliveries. Saving it as enabled resets that counter.') }}
                </flux:callout.text>
            </flux:callout>
        @endif

        <x-card-details-input label="{{ __('URL') }}" wire:model="url" />
        @error('url') <flux:text class="text-red-600">{{ $message }}</flux:text> @enderror

        <x-card-details-input label="{{ __('Basic Auth Username (optional)') }}" wire:model="basicAuthUsername" />
        <x-card-details-input label="{{ __('Basic Auth Password') }}" type="password" wire:model="basicAuthPassword" />
        @if($hasStoredPassword)
            <flux:text size="sm" class="text-zinc-500">{{ __('A password is stored. Leave blank to keep it.') }}</flux:text>
        @endif

        <flux:text size="sm" class="text-zinc-500">
            {{ __('The Idempotency-Key header carries the event id. Deduplicate on it, because delivery is at least once.') }}
        </flux:text>
    </x-card>

    <x-card>
        <flux:callout icon="eye">
            <flux:callout.text>
                {{ __('This rule would match') }}
                <strong>{{ $preview['matched'] }}</strong>
                {{ __('of the last') }} {{ $preview['total'] }} {{ __('recorded events.') }}
            </flux:callout.text>
        </flux:callout>

        <div class="mt-4 flex items-center justify-end gap-2">
            <flux:button variant="ghost" href="{{ route('iscpp.rules.index') }}" wire:navigate>{{ __('Cancel') }}</flux:button>
            <x-a-button wire:click="save">{{ $ruleId ? __('Save Rule') : __('Create Rule') }}</x-a-button>
        </div>
    </x-card>
</div>
