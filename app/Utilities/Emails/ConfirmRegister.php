<?php

namespace Utilities\Emails;


class ConfirmRegister {
    static function html(string $username, string $baseUrl, string $confirmationUid): string {
        return <<< HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
</head>
<body style="font-family: Helvetica, Arial, sans-serif; margin 16px; max-width: 700px; width: 97%;">
    <header>
        <table style="width: 100%; height: 128px; background-color: hotpink; border-radius: 12px;">
            <tr>
                <td align="center" valign="middle" style="text-align: center;">
                    <h1 style="font-family: monospace">≡ Kittens ≡</h1>
                </td>
            </tr>
        </table>
    </header>
    <main>
        <h1 style="text-align: center;">Confirm Registration</h1>
        <p style="padding: 0 32px;">
        Hi {$username}! Confirm your account registration to access your account. You have 24 hours to complete your registration.
        </p>
        
        <table style="width: 100%; margin: 32px 0;">
            <tr>
                <td align="center" valign="middle">
                    <a href="{$baseUrl}?confirmationId={$confirmationUid}"
                       style="background-color: pink; padding: 16px 32px; border-radius: 12px; color: black;">
                        Confirm Registration
                    </a>
                </td>
            </tr>
        </table>
        
        <div style="padding: 24px 32px;">
            <p>If the button above isn't working, copy the following link and paste it in the browser:</p>
            <a href="{$baseUrl}?confirmationId={$confirmationUid}">{$baseUrl}?confirmationId={$confirmationUid}</a>
        </div>
    </main>
    
    
    
    <footer style="width: 97%; height: 164px; max-width: 700px; background-color: hotpink; border-radius: 12px; margin-top: 64px; bottom: 8px; position: absolute;">
        <table style="width: 100%; padding-top: 16px;">
        <tr>
            <td valign="middle" style="text-align: center; width: 33.3333%;">
                <a href="https://github.com/C4lopsitta/cat-frontend" style="color: black;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-github" viewBox="0 0 16 16">
                        <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09 2 .27c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8"/>
                    </svg>
                    <p style="margin-top: 4px;">Frontend</p>
                </a>
            </td>
            <td valign="middle" style="text-align: center; width: 33.3333%;">
                <a href="https://github.com/C4lopsitta/cat-backend" style="color: black;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-github" viewBox="0 0 16 16">
                        <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09 2 .27c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8"/>
                    </svg>
                    <p style="margin-top: 4px;">Backend</p>
                </a>
            </td>
            <td valign="middle" style="text-align: center; width: 33.3333%;;">
                <a href="https://github.com/C4lopsitta/cat-docs" style="color: black;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-github" viewBox="0 0 16 16">
                        <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09 2 .27c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8"/>
                    </svg>
                    <p style="margin-top: 4px;">Documentation</p>
                </a>
            </td>
        </tr>

    </table>
    <table style="width: 100%; padding-bottom: 16px;">
        <tr>
            <td valign="middle" align="center" style="text-align: center;">
                <p>Project licensed under <a href="https://www.gnu.org/licenses/gpl-3.0.en.html#license-text">GNU GPLv3</a> license</p>
            </td>
        </tr>
    </table>
    </footer>
</body>
</html>
HTML;
    }

    static function plainText(string $username, string $baseUrl, string $confirmationUid) {
        return "Hi $username!\nOpen the following link in the browser to complete your account registration.\n$baseUrl?confirmationId=$confirmationUid";
    }
}
