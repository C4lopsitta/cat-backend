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

namespace Utilities;

use Random\RandomException;

class Uid {
  static function verify(string $uid): bool {
    $uid = self::format($uid);
    if(!preg_match("/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i", $uid)) return false;
    if(strlen(join(explode("-", $uid))) == 32) return true;
    return false;
  }

  /**
   * @throws RandomException
   */
  static function generate(): string {
    $data = random_bytes(16);

    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return strtolower(vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)));
  }

  static function format(string $uid): string {
    $uid = self::compact($uid);
    return strtolower(substr($uid, 0, 8) . '-' .
        substr($uid, 8, 4) . '-' .
        substr($uid, 12, 4) . '-' .
        substr($uid, 16, 4) . '-' .
        substr($uid, 20, 12));
  }

  static function compact(string $uid): string {
    return strtolower(str_replace("-", "", $uid));
  }

}


