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

namespace Jsons;

use Model\User;
use Utilities\Uid;

class Users {
    // TODO)) Add pagination
    static function listUsers(array $users, ?int $page, int $itemsPerPage): string {
        $usersJsonList = [];

        foreach ($users as $user) {
            $uid = Uid::format($user->getUid());
            $usersJsonList[] = <<< JSON
{
    "uid": "{$uid}",
    "username": "{$user->getUsername()}",
    "image": "",
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

    static function user(User $user, string $ownedCats = ""): string {
        $uid = Uid::format($user->getUid());
        return <<< JSON
{
    "uid": "{$uid}",
    "username": "{$user->getUsername()}",
    "image": "",
    "imageMime": "{$user->getImageMimeType()}",
    "description": "{$user->getDescription()}",
    "pronouns": "{$user->getPronouns()}",
    "cats": [
      {$ownedCats}
    ],
    "wishlist": [
    ]
}
JSON;
    }
    static function userRegistrationResponse(string $username, string $email, string $uid): string {
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        return <<< JSON
{
  "username": "{$username}",
  "email": "{$email}",
  "uid": "{$uid}"
}
JSON;

    }

    public static function userExistsResponse(string $email) {
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        return <<< JSON
{
  "error": "User already exists",
  "email": "{$email}",
  "status": 401
}
JSON;
    }

    public static function newUserTokenResponse(string $uid, string $token, int $expiresIn) {
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        return <<< JSON
{
  "uid": "{$uid}",
  "token": "{$token}",
  "expiresIn": {$expiresIn}
}
JSON;
    }

    public static string $tokenExpiredOrUsed = <<< JSON
{
  "error": "Token expired",
  "status": 401
}
JSON;

}