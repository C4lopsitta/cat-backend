<?php
/*
 * Copyright (c) 2025.
 *
 * Code is licensed under GNU GPLv3 License and available in the Copying file.
 * Code was written by:
 * - Simone Robaldo ( simone.robaldo at itiscuneo.eu )
 * - Giulia Vadelli ( giulia.vadelli at itiscuneo.eu )
 * - Daniele Torchio ( daniele.torchio at itiscuneo.eu )
 */

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

    public static function verify($password, $hash): bool {
        return password_verify($password, $hash);
    }
}