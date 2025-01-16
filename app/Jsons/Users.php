<?php

namespace Jsons;

use Utilities\Uid;

class Users {
    // TODO)) Add pagination
    static function listUsers(array $users, ?int $page, int $itemsPerPage): string {
        $usersJsonList = [];

        foreach ($users as $user) {
            $uid = Uid::generate();
            $usersJsonList[] = <<< JSON
{
        "uid": "{$uid}",
        "username": "Bob",
        "image": "base64 ........",
        "imageMime": "image/gif",
        "description": "I'm bob and i love cats. I live in catworld and have 2^64 cats in my house. Their names are the fibonacci sequence. Except cat number 42 whose name is Megatron.",
        "pronouns": "hee/hee",
        "cats": [
          {
            "descr": "the father",
            "value": 1000
          }
        ],
        "wishlist": [
          "ffffffff-ffff-ffff-ffff-ffffffffffff"
        ]
    }
JSON;
        }

        $usersJsonList = join(", ", $usersJsonList);

        return <<< JSON
[
  {$usersJsonList}
]
JSON;
    }

    static function userRegistrationResponse(string $username, string $email, string $uid): string {
        return <<< JSON
{
  "username": "{$username}",
  "email": "{$email}",
  "uid": "{$uid}"
}
JSON;

    }

    public static function userExistsResponse(string $email) {
        return <<< JSON
{
  "error": "User already exists",
  "email": "{$email}",
  "status": 401
}
JSON;
    }
}