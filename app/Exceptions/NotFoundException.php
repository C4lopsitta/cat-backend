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

use Enums\NotFoundReason;
use Exception;
use Exceptions\BaseApiException;

class NotFoundException extends BaseApiException {
    public static int $HTTP_STATUS_CODE = 404;
    public NotFoundReason $reason;

    public function __construct(NotFoundReason $reason = NotFoundReason::PATH_NOT_FOUND, $message = "", $code = 0, Exception $previous = null) {
        $this->reason = $reason;
        parent::__construct($message, $code, $previous);
    }

    public function toJson(): string {
        $reason = $this->reason->toReason();

        return <<< JSON
{
  "error": "Not found",
  "reason": {$reason},
  "status": 404
}
JSON;
    }

    public function toLog(): string {
        $time = date("H:i d/m/Y", time());
        return "[INFO] Request for unknown path/cat/user made at {$time}";
    }
}