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

namespace Exceptions;

use Exceptions\UnauthorizedException;

class UserAlreadyExistsException extends UnauthorizedException {
    function toJson(): string {
        return <<< JSON
{
  "error": "User already exists",
  "status": 401
}
JSON;
    }

    public function toLog(): string {
        $reason = $this->reason->toReason();
        $time = date("H:i d/m/Y", time());
        return "[INFO] User creation request made for already existing user account at {$time}";
    }

}