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


