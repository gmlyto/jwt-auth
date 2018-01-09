<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Sean D19sp <dinho19sp@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace D19sp\JWTAuth;

use Carbon\Carbon;

class Utils
{
    /**
     * Get the Carbon instance for the current time.
     *
     * @return \Carbon\Carbon
     */
    public static function now()
    {
        return Carbon::now();
    }

    /**
     * Get the Carbon instance for the timestamp.
     *
     * @param  int  $timestamp
     * @return \Carbon\Carbon
     */
    public static function timestamp($timestamp)
    {
        return Carbon::createFromTimeStampUTC($timestamp);
    }
}
