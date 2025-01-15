<?php

namespace Utilities;

class CommonJsons {
  static string $Base = <<< JSON
{
  "version": "1",
  "docs": "https://c4lopsitta.github.io/cat-docs/index_md.html"
}
JSON;

  static string $NotFound = <<< JSON
{
  "error": "Path not found",
  "status": 404
}
JSON;

  static string $InvalidUID = <<< JSON
{
  "error": "The UID is badly formatted or invalid",
  "status": 400
}
JSON;

  static string $MethodNotAllowed = <<< JSON
{
  "error": "Method not allowed",
  "status": 405
}
JSON;

  static function BadRequest(array $fieldErrors = []): string {
      $fieldErrors = join(", ", $fieldErrors);
      return <<< JSON
{
  "error": "Bad Request",
  "fieldErrors": [{$fieldErrors}],
  "status": 400
}
JSON;
  }




}
