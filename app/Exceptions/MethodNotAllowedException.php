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

use Exceptions\BaseApiException;

class MethodNotAllowedException extends BaseApiException {
    public static int $HTTP_STATUS_CODE = 405;

    public function toJson(): string {
        return json_encode([
            'error' => "Method Not Allowed",
            'method' => $this->message,
            'status' => 405,
        ]);
    }

    public function toLog(): string {
        $time = date("H:i d/m/Y", time());
        return "[INFO] Request with unallowed method {$this->message} made at {$time}";
    }
}