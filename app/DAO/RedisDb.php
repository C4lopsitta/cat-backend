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

namespace DAO;

use Enums\UnauthorizedReason;
use Exception;
use Exceptions\UnauthorizedException;
use http\Exception\BadMessageException;
use Redis;
use RedisException;
use Utilities\Uid;

class RedisDb {
    static private ?Redis $instance = null;

    /**
     * @throws Exception
     */
    static public function connect() {
        try {
            self::$instance = new Redis();

            self::$instance->connect('redis', 6379);
        } catch (Exception $e) {
            self::$instance = null;
            throw $e;
        }
    }

    static public function storeUserToken(string $token, string $userUid): void {
        if(!self::$instance) {
            throw new RedisException("RedisDb connection not established");
        }

        $userUid = Uid::compact($userUid);

        self::$instance->setex('token:'.$token, 3600, $userUid);
    }

    /**
     * @throws UnauthorizedException
     * @return string User token
     */
    static public function validateUserToken(string $token): string {
        if(!self::$instance) {
            throw new RedisException("RedisDb connection not established");
        }

        $redisUserUid = self::$instance->get("token:$token");
        $redisUserUid = Uid::compact($redisUserUid);

        if($redisUserUid == null) {
            throw new UnauthorizedException(UnauthorizedReason::INVALID_TOKEN);
        }
        return $redisUserUid;
    }

    static public function invalidateUserTokens(string $userUid): void {
        if(self::$instance == null) {
            throw new RedisException('Redis connection not established');
        }

        $userUid = Uid::compact($userUid);

        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        self::$instance->del("token:{$userUid}");
    }

    /**
     * Generates a token to send to the user for account verification that expires after 24 hours.
     * @param string $userUid
     * @return string
     */
    static public function generateAndStoreAccountConfirmToken(string $userUid): string {
        $userUid = Uid::compact($userUid);
        $accountConfirmToken = hash('sha224', $userUid . rand(100000, 999999));

        if(self::$instance == null) {
            throw new RedisException('Redis connection not established');
        }

        self::$instance->setex("confirmToken:$accountConfirmToken", 60 * 60 * 24, $userUid);

        return $accountConfirmToken;
    }

    /**
     * Verifies the validity of the user token, if valid it will delete it from the Redis Instance and return the user's UID, otherwise it will return Null;
     * @param string $accountConfirmToken
     * @return string|null
     */
    static public function verifyAccountConfirmToken(string $accountConfirmToken): ?string {
        if(self::$instance == null) {
            throw new RedisException('Redis connection not established');
        }

        $userUid = self::$instance->get("confirmToken:".$accountConfirmToken);
        self::$instance->del("confirmToken:".$accountConfirmToken);

        return ($userUid != null) ? Uid::format($userUid) : null;
    }







    static public function storeTestingToken(string $token): void {
        if(!self::$instance) {
            throw new RedisException("RedisDb connection not established");
        }

        self::$instance->setex('testingToken', 60 * 60 * 24, $token);
    }

    static public function testTestingToken(string $token): bool {
        if(!self::$instance) {
            throw new RedisException("RedisDb connection not established");
        }

        if( strcmp(self::$instance->get("testingToken"), $token) == 0 ) {
            return true;
        }
        return false;
    }
}