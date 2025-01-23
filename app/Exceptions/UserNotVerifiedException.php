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


use Exception;

class UserNotVerifiedException extends BaseApiException {
    public static int $HTTP_STATUS_CODE = 403;

    function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    function toJson(): string {
        return <<< JSON
{
  "error": "User account is not verified",
  "status": 403
}
JSON;
    }

    public function toLog(): string {
        $time = date("H:i d/m/Y", time());
        return "[INFO] User request with unverified account made at {$time}";
    }
}