<?php
    class User{
        private string $uid;
        private string $username;
        private string $email;
        private string $passwordHash;

        /**
         * @param string $uid
         * @param string $username
         * @param string $email
         * @param string $passwordHash
         */
        public function __construct(string $uid, string $username, string $email, string $passwordHash) {
            $this->uid = $uid;
            $this->username = $username;
            $this->email = $email;
            $this->passwordHash = $passwordHash;
        }

        public function getUid(): string {
            return $this->uid;
        }

        public function setUid(string $uid): void {
            $this->uid = $uid;
        }

        public function getUsername(): string {
            return $this->username;
        }

        public function setUsername(string $username): void {
            $this->username = $username;
        }

        public function getEmail(): string {
            return $this->email;
        }

        public function setEmail(string $email): void {
            $this->email = $email;
        }

        public function getPasswordHash(): string {
            return $this->passwordHash;
        }

        public function setPasswordHash(string $passwordHash): void {
            $this->passwordHash = $passwordHash;
        }

    }