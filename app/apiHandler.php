<?php

use BaseHandlers\Cats;
use BaseHandlers\Users;
use Utilities\Uid;
use Utilities\CommonJsons;

$apiBase = "/api/v1";
$uri = str_replace($apiBase, "", $_SERVER['REQUEST_URI']);

$uriParts = explode("/", $uri);

// [INFO] Switch the base part of the URI

switch ($uriParts[0]) {
  case "users":
    Users::handler($uriParts);
    break;
  case "cats":
    Cats::handler($uriParts);
    break;
  default:
    echo CommonJsons::$Base;
    break;
}

header("Content-type: application/json");
