<?php

namespace DAO;

use Exception;
use RedisException;

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

    static public function generateAndStoreAccountConfirmToken(string $userUid): string {
        $accountConfirmToken = hash('sha224', $userUid . rand(100000, 999999));

        if(self::$instance == null) {
            throw new RedisException('Redis connection not established');
        }

        self::$instance->setex("confirmToken:$accountConfirmToken", 60 * 60 * 24, $userUid);

        return $accountConfirmToken;
    }

}