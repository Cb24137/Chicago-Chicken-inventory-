<?php

namespace App\Support;

// Runs code without writing activity-log entries or sending notifications.
// Used when loading demo data, which is not something a staff member did.
class Quiet
{
    private static bool $active = false;

    public static function run(callable $callback): mixed
    {
        $wasActive = self::$active;
        self::$active = true;

        try {
            return $callback();
        } finally {
            self::$active = $wasActive;
        }
    }

    public static function active(): bool
    {
        return self::$active;
    }
}
