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

enum NotFoundReason {
    case PATH_NOT_FOUND;
    case USER_NOT_FOUND;
    case CAT_NOT_FOUND;

    function toReason(): string {
        return match ($this) {
            self::CAT_NOT_FOUND => "Requested cat could not be found",
            self::USER_NOT_FOUND => "Requested user could not be found",
            self::PATH_NOT_FOUND => "Requested path could not be found",
        };
    }
}
