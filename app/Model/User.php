<?php

namespace Model;
class User
{
    private string $uid;
    private string $username;
    private string $email;
    private ?string $image;
    private ?string $imageMimeType;
    private ?string $description;
    private ?string $pronouns;
    private string $passwordHash;
    private bool $isAccountConfirmed;

    /**
     * @param string $username
     * @param string $uid
     * @param string $email
     * @param string|null $image
     * @param string|null $imageMimeType
     * @param string|null $description
     * @param string|null $pronouns
     * @param string $passwordHash
     * @param bool $isAccountConfirmed
     */
    public function __construct(string $username, string $uid, string $email, ?string $image, ?string $imageMimeType, ?string $description, ?string $pronouns, string $passwordHash, bool $isAccountConfirmed) {
        $this->username = $username;
        $this->uid = $uid;
        $this->email = $email;
        $this->image = $image;
        $this->imageMimeType = $imageMimeType;
        $this->description = $description;
        $this->pronouns = $pronouns;
        $this->passwordHash = $passwordHash;
        $this->isAccountConfirmed = $isAccountConfirmed;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function setUid(string $uid): void
    {
        $this->uid = $uid;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function getImage(): ?string {
        return $this->image;
    }

    public function setImage(?string $image): void {
        $this->image = $image;
    }

    public function getImageMimeType(): ?string {
        return $this->imageMimeType;
    }

    public function setImageMimeType(?string $imageMimeType): void {
        $this->imageMimeType = $imageMimeType;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getPronouns(): ?string {
        return $this->pronouns;
    }

    public function setPronouns(?string $pronouns): void {
        $this->pronouns = $pronouns;
    }

    public function isAccountConfirmed(): bool {
        return $this->isAccountConfirmed;
    }

    public function setIsAccountConfirmed(bool $isAccountConfirmed): void {
        $this->isAccountConfirmed = $isAccountConfirmed;
    }



}