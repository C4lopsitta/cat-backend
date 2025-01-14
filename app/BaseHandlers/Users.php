<?php

namespace BaseHandlers;

use Utilities\CommonJsons;
use Utilities\Uid;

class Users {
  static function handler(array $uriParts) {
    if(sizeof($uriParts) == 1) {
      // root users not allowed, 404
      http_response_code(404);
      echo CommonJsons::$NotFound;
      return;
    }

    if(sizeof($uriParts) == 2) {
      if(!Uid::verify($uriParts[1])) {
        http_response_code(400);
        echo CommonJsons::$InvalidUID;
        return;
      }


    }

  }
}
