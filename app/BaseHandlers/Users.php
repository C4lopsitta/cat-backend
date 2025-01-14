<?php

namespace BaseHandlers;

use Utilities\CommonJsons;
use Utilities\Uid;

class Users {
  static function handler(array $uriParts): void {
    if(sizeof($uriParts) == 1) {
        echo \Jsons\Users::listUsers(["a", "b"]);
      return;
    }

    if(sizeof($uriParts) >= 2) {
        if($uriParts[1] == "register") {
            // register
            return;
        } elseif($uriParts[1] == "authenticate") {
            //auth
            return;
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
    echo CommonJsons::$NotFound;
  }
}
