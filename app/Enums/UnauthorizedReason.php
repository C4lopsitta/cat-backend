<?php
/*
 * Copyright (c) 2025.
 *
 * Code is licensed under GNU GPLv3 License and available in the Copying file.
 * Code was written by:
 * - Simone Robaldo ( simone.robaldo at itiscuneo.eu )
 * - Giulia Vadelli ( giulia.vadelli at itiscuneo.eu )
 * - Daniele Torchio ( daniele.torchio at itiscuneo.eu )
 */

namespace Enums;

enum UnauthorizedReason {
    case TOKEN_EXPIRED;
    case NO_TOKEN_PROVIDED;
    case INVALID_TOKEN;
    case UNDEFINED;
    case NOT_ALLOWED;



    function toReason(): string {
        return match ($this) {
            self::TOKEN_EXPIRED => 'Token expired',
            self::NO_TOKEN_PROVIDED => 'No token provided',
            self::INVALID_TOKEN => 'Invalid token',
            self::NOT_ALLOWED => 'User is not allowed to perform this action',
            default => 'Unauthorized'
        };
    }
}
