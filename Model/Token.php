<?php
class Token{
    private string $token;
    private int $expirationDate;
    private string $userUID;

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

    public function getToken(): string {
        return $this->token;
    }

    public function setToken(string $token): void {
        $this->token = $token;
    }

    public function getExpirationDate(): int {
        return $this->expirationDate;
    }

    public function setExpirationDate(int $expirationDate): void {
        $this->expirationDate = $expirationDate;
    }

    public function getUserUID(): string {
        return $this->userUID;
    }

    public function setUserUID(string $userUID): void {
        $this->userUID = $userUID;
    }

}