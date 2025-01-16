<?php

namespace Jsons;

use Utilities\Uid;

class Users {
    // TODO)) Add pagination
    static function listUsers(array $users, ?int $page, int $itemsPerPage): string {
        $usersJsonList = [];

        foreach ($users as $user) {
            $usersJsonList[] = <<< JSON
{
        "uid": "{$user->getUid()}",
        "username": "{$user->getUsername()}",
        "image": "TODO Convert image to base64",
        "imageMime": "{$user->getImageMimeType()}",
        "description": "{$user->getDescription()}",
        "pronouns": "{$user->getPronouns()}",
        "cats": [
        ],
        "wishlist": [
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

    public static function newUserTokenResponse(string $token, int $expiresIn) {
        return <<< JSON
{
  "token": "{$token}",
  "expiresIn": {$expiresIn}
}
JSON;
    }
}