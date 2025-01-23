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

require "vendor/autoload.php";

use BaseHandlers\Cats;
use BaseHandlers\Users;
use Exceptions\BaseApiException;
use Utilities\CommonJsons;

ini_set('display_errors', 0);
ini_set('log_errors', 1);

set_error_handler(function ($severity, $message, $file, $line) {
    http_response_code(500); // Set the HTTP status code
    $json = [
       'error' => "Server Error",
       'status' => 500,
    ];

    if(getenv("DEBUG_MODE") == "true") {
        $json["file"] = $file;
        $json["line"] = $line;
        $json["severity"] = $severity;
        $json["errorDetails"] = $message;
    }

    echo json_encode($json);


    exit; // Stop script execution after handling the error
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        http_response_code(500);
        $json = [
            'error' => "Fatal Error",
            'status' => 500
        ];

        if(getenv("DEBUG_MODE") == "true") {
            $json["file"] = $error["file"];
            $json["line"] = $error["line"];
            $json["errorDetails"] = $error["message"];
            $json["type"] = $error["type"];
        }

        echo json_encode($json);
    }
});


$apiBase = "/api/v1/";
$uri = str_replace($apiBase, "", $_SERVER['REQUEST_URI']);

$uriParts = explode("/", $uri);
header("Content-type: application/json");
// [INFO] Switch the base part of the URI

try {
    switch ($uriParts[0]) {
        case "users":
            echo Users::handler($uriParts);
            break;
        case "cats":
            Cats::handler($uriParts);
            break;
        case "info":
            echo CommonJsons::$Info;
            break;
        default:
            echo CommonJsons::$NotFound;
            break;
    }
} catch (BaseApiException $ex) {
    http_response_code($ex::$HTTP_STATUS_CODE);
    error_log($ex->toLog());
    echo $ex->toJson();
}

