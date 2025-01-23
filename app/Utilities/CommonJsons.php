<?php /** @noinspection ALL */
/** @noinspection ALL */

/*
 * Copyright (c) 2025.
 *
 * Code is licensed under GNU GPLv3 License and available in the Copying file.
 * Code was written by:
 * - Simone Robaldo ( simone.robaldo at itiscuneo.eu )
 * - Giulia Vadelli ( giulia.vadelli at itiscuneo.eu )
 * - Daniele Torchio ( daniele.torchio at itiscuneo.eu )
 */

namespace Utilities;


use Exception;

class
CommonJsons {
  static string $Info = <<< JSON
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
}
