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
use Utilities\Uid;
use Utilities\CommonJsons;

set_error_handler(function ($severity, $message, $file, $line) {
    http_response_code(500); // Set the HTTP status code
    $json = [
       'error' => $message,
       'status' => 500,
    ];

    if(getenv("DEBUG_MODE") == "true") {
        $json["file"] = $file;
        $json["line"] = $line;
    }

    echo json_encode($json);
    exit; // Stop script execution after handling the error
});

$apiBase = "/api/v1/";
$uri = str_replace($apiBase, "", $_SERVER['REQUEST_URI']);

$uriParts = explode("/", $uri);
header("Content-type: application/json");
// [INFO] Switch the base part of the URI

switch ($uriParts[0]) {
  case "users":
      Users::handler($uriParts);
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


