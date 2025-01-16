<?php

namespace Utilities;

class Password {
    private static $hashingOptions = [
       'memory_cost' => 1 << 16,
       'time_cost' => 8,
       'threads' => 4
    ];

    public static function hash($password): string {
        return password_hash($password, PASSWORD_ARGON2ID, self::$hashingOptions);
    }
}