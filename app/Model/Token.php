<?php

namespace Model;

use Utilities\Uid;

/**
 *
 */
class Token
{
    private string $token;
    private int $expirationDate;
    private string $userUID;

    /**
     * Generates a new User token from the User UID and give an optional duration
     * @param string $userUID
     * @param int $durationSecs
     * @throws \Random\RandomException
     * @return Token
     */
    static function generate(string $userUID, int $durationSecs = 3600): Token {
        $token = hash("sha-512", bin2hex( random_bytes(64)) . "catapi");
        $expiresAt = time() + $durationSecs;

        if(strlen($userUID) > 32) {
            $userUID = Uid::compact($userUID);
        }

        return new self($token, $expiresAt, $userUID);
    }

    /**
     * @param string $token
     * @param int $expirationDate
     * @param string $userUID
     */
    public function __construct(string $token, int $expirationDate, string $userUID) {
        $this->token = $token;
        $this->expirationDate = $expirationDate;
        $this->userUID = $userUID;
    }

    private static function getSalt(string $token): string {
        return $token[0] . $token[8] . $token[16] . $token[32] . $token[64] . $token[128];
    }

    public function toDatabaseHash(): string {
        return hash("sha512", $this->token . self::getSalt($this->token));
    }

    public static function getDatabaseHash(string $token): string {
        return hash("sha512", $token . self::getSalt($token));
    }

    public function verifyValidity(): bool {
        return time() < $this->expirationDate;
    }

    public function getToken(): string {
        return $this->token;
    }

    public function getExpirationDate(): int {
        return $this->expirationDate;
    }

    public function getUserUID(): string {
        return $this->userUID;
    }

}