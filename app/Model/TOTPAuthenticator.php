<?php
use RobThree\Auth\TwoFactorAuth;
use DAO\UserDAO;

class TOTPAuthenticator {
    private ?TwoFactorAuth $tfa = null;

    /**
     * @param TwoFactorAuth|null $tfa
     */
    public function __construct() {
        $this->tfa = new TwoFactorAuth(
            new BaconQrCodeProvider(),
            null,
            6,
            240,
        );
    }

    private function activate2FA(string $userUID): string {
        $secret = $this->tfa->createSecret();

        UserDAO::storeUserSecret($userUID, $secret);

        return $secret;
    }

    public function getTFA(): ?TwoFactorAuth {
        return $this->tfa;
    }

    public function setTFA(?TwoFactorAuth $tfa): void {
        $this->tfa = $tfa;
    }

    public function verifyCode(string $userUID, string $userInputCode): bool {
        $secret = UserDAO::readTFA($userUID);
        return $this->tfa->verifyCode($secret, $userInputCode);
    }

    private function generateCode(string $userUID){
        $secret = UserDAO::readTFA($userUID);
        $code = $this->tfa->getCode($secret);

        return $code;
    }

    private function generateQRCode(string $userUID): string {
        $secret = UserDAO::readTFA($userUID);
        $qrCode = $this->tfa->getQRCodeImageAsDataUri($secret);
         return $qrCode;
    }
}