<?php

namespace Utilities;

class Uid {
  static function verify(string $uid): bool {
    if(!preg_match("/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/i", $uid)) return false;

    if(strlen($uid) == 32) return true;
    if(strlen(join(explode("-", $uid))) == 32) return true;

    return false;
  }

  static function generate(): string {
    $data = random_bytes(16);

    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  }

  static function format(string $uid): string {
    return substr($uid, 0, 8) . '-' .
        substr($uid, 8, 4) . '-' .
        substr($uid, 12, 4) . '-' .
        substr($uid, 16, 4) . '-' .
        substr($uid, 20, 12);
  }

  static function compact(string $uid): string {
    return str_replace("-", "", $uid);
  }

}

?>
