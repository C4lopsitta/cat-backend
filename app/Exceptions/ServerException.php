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
use Throwable;

class ServerException extends BaseApiException {
    public static int $HTTP_STATUS_CODE = 500;

    public array $trace;
    public string $thrownIn;

    function __construct(string $message = "", int $code = 0, Throwable $previous = null, array $trace = [], string $thrownIn = "") {
        $this->trace = $trace;
        $this->thrownIn = $thrownIn;
        parent::__construct($message, $code, $previous);
    }

    public function toJson(): string {
        return json_encode([
            "error" => "Server error",
            "trace" => $this->trace,
            "exception" => $this->message,
            "thrown_in" => $this->thrownIn,
            "status" => 500
        ]);
    }

    public function toLog(): string {
        $time = date("H:i d/m/Y", time());

        $trace = implode("\n", array_map(function ($entry) {
            // Only handle arrays, convert others directly to strings
            if (is_array($entry)) {
                return implode(", ", $entry); // Join array elements into one string
            } else {
                return (string) $entry; // Convert scalar values to strings
            }
        }, $this->trace));

        return "[ERROR] Server Error happened at {$time}\n[EXCEPTION] {$this->message}\n[TRACE] {$trace}";
    }
}