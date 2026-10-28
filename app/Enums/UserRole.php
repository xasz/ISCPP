<?php

namespace App\Enums;

enum UserRole: string
{
    /** Everything, including user management and general settings. */
    case SuperAdmin = 'superadmin';

    /** Everything except user management and general settings. */
    case Editor = 'editor';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => __('Superadmin'),
            self::Editor => __('Editor'),
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::SuperAdmin => 'violet',
            self::Editor => 'sky',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $role) => [$role->value => $role->label()])->all();
    }
}
