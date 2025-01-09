<?php

namespace Model;
class Token {
  private string $token;
  private int $expirationDate;

  /**
   * @param string $token
   * @param int $expirationDate
   */
  public function __construct(string $token, int $expirationDate) {
    $this->token = $token;
    $this->expirationDate = $expirationDate;
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
}