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
use Exceptions\BaseApiException;

class BadRequestException extends BaseApiException {
    public static int $HTTP_STATUS_CODE = 400;

    public array $field_errors;

    public function __construct(array $field_errors = [], $message = "", $code = 0, Exception $previous = null) {
        $this->field_errors = $field_errors;
        parent::__construct($message, $code, $previous);
    }

    public function toJson(): string {
        return json_encode([
           'error' => "Bad Request",
            'field_errors' => $this->field_errors,
            'status' => self::$HTTP_STATUS_CODE
        ]);
    }

    public function toLog(): string {
        $time = date("H:i d/m/Y", time());
        return "[INFO] Badly formatted request made at {$time}";
    }
}