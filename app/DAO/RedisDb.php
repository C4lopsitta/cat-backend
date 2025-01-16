<?php

namespace DAO;

use Exception;
use RedisException;
use Utilities\Uid;

class RedisDb {
    static private ?Redis $instance = null;

    /**
     * @throws Exception
     */
    static public function connect() {
        try {
            $redis = new Redis();

            $redis->connect('redis', 6379);
        } catch (Exception $e) {
            $redis = null;
            throw $e;
        }
    }

    static public function storeUserToken(string $token, string $userUid) {

    }

    static public function validateUserToken(string $token): bool {

    }

    static public function invalidateUserTokens(string $userUid) {
        if(self::$instance == null) {
            throw new RedisException('Redis connection not established');
        }

        $userUid = Uid::compact($userUid);

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

}