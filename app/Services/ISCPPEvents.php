<?php

namespace App\Services;

/**
 * Suppression switch for ISCPP event emission.
 *
 * Used to keep bulk imports out of the event log. This is a nesting counter rather than
 * a boolean, and always restores in a finally block: a boolean left set by a thrown
 * exception would silently mute a long lived queue worker for the rest of its life.
 */
final class ISCPPEvents
{
    private static int $muted = 0;

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function mute(callable $callback): mixed
    {
        self::$muted++;

        try {
            return $callback();
        } finally {
            self::$muted--;
        }
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function muteIf(bool $condition, callable $callback): mixed
    {
        return $condition ? self::mute($callback) : $callback();
    }

    public static function muted(): bool
    {
        return self::$muted > 0;
    }
}
