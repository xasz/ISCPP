<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One payload condition on a rule. All conditions on a rule are ANDed.
 *
 * OR is expressed as two rules pointing at the same webhook, which keeps both the
 * matcher and the rule builder understandable.
 */
class ISCPPEventRuleCondition extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'iscpp_event_rule_conditions';

    protected $fillable = [
        'iscpp_event_rule_id',
        'path',
        'operator',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function operatorOptions(): array
    {
        return [
            'eq' => __('is equal to'),
            'neq' => __('is not equal to'),
            'in' => __('is one of'),
            'not_in' => __('is not one of'),
            'lt' => __('is less than'),
            'gte' => __('is greater than or equal to'),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function passes(array $payload): bool
    {
        $actual = data_get($payload, $this->path);

        // A missing path never matches, whatever the operator. Without this, "neq high"
        // would be true for every event that has no severity at all.
        if ($actual === null) {
            return false;
        }

        if (is_array($actual)) {
            return false;
        }

        return match ($this->operator) {
            'eq' => $this->asString($actual) === $this->asString($this->singleValue()),
            'neq' => $this->asString($actual) !== $this->asString($this->singleValue()),
            'in' => in_array($this->asString($actual), $this->listValue(), true),
            'not_in' => ! in_array($this->asString($actual), $this->listValue(), true),
            'lt' => $this->compareNumeric($actual, fn ($a, $b) => $a < $b),
            'gte' => $this->compareNumeric($actual, fn ($a, $b) => $a >= $b),
            default => false,
        };
    }

    private function compareNumeric(mixed $actual, callable $comparator): bool
    {
        $expected = $this->singleValue();

        // Fail closed rather than let PHP coerce "high" < 50 into something meaningless.
        if (! is_numeric($actual) || ! is_numeric($expected)) {
            return false;
        }

        return $comparator((float) $actual, (float) $expected);
    }

    private function singleValue(): mixed
    {
        $value = $this->value;

        return is_array($value) ? ($value[0] ?? null) : $value;
    }

    /**
     * @return array<int, string>
     */
    private function listValue(): array
    {
        $value = $this->value;

        return collect(is_array($value) ? $value : [$value])
            ->map(fn ($item) => $this->asString($item))
            ->all();
    }

    private function asString(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    public function describe(): string
    {
        $operators = self::operatorOptions();
        $value = collect((array) $this->value)->implode(', ');

        return trim(sprintf('%s %s %s', $this->path, $operators[$this->operator] ?? $this->operator, $value));
    }
}
