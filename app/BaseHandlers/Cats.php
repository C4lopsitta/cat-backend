<?php

namespace BaseHandlers;

use Utilities\CommonJsons;
use Utilities\Uid;

class Cats {
  static function handler(array $uriParts) {

    if(sizeof($uriParts) == 1) {
      // list cats
      return;
    }

    if(sizeof($uriParts) >= 2) {
      if($uriParts[1] == "buy") {
        // generic path /cats/something
      } elseif(strlen($uriParts[1]) == 32 + 4) {
        if (!Uid::verify($uriParts[1])) {
          http_response_code(400);
          echo CommonJsons::$InvalidUID;
          return;
        }elseif (Uid::verify($uriParts[1])) {
          if (sizeof($uriParts) == 3) {
            // uid + some action
            return;
          }else {
            // only uid was given
            return;
          }
        }
      } else {
        http_response_code(404);
        echo CommonJsons::$NotFound;
        return;
      }
    }

    http_response_code(404);
    echo CommonJsons::$NotFound;  }
}
