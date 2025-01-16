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
    public function __construct(string $token, int $expirationDate, string $userUID)
    {
        $this->token = $token;
        $this->expirationDate = $expirationDate;
        $this->userUID = $userUID;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getExpirationDate(): int
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(int $expirationDate): void
    {
        $this->expirationDate = $expirationDate;
    }

    public function getUserUID(): string
    {
        return $this->userUID;
    }

    public function setUserUID(string $userUID): void
    {
        $this->userUID = $userUID;
    }

}