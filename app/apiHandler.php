<?php

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


