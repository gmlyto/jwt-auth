<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Sean D19sp <dinho19sp@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace D19sp\JWTAuth\Claims;

class Factory
{
    /**
     * @var array
     */
    private static $classMap = [
        'aud' => 'D19sp\JWTAuth\Claims\Audience',
        'exp' => 'D19sp\JWTAuth\Claims\Expiration',
        'iat' => 'D19sp\JWTAuth\Claims\IssuedAt',
        'iss' => 'D19sp\JWTAuth\Claims\Issuer',
        'jti' => 'D19sp\JWTAuth\Claims\JwtId',
        'nbf' => 'D19sp\JWTAuth\Claims\NotBefore',
        'sub' => 'D19sp\JWTAuth\Claims\Subject',
    ];

    /**
     * Get the instance of the claim when passing the name and value.
     *
     * @param  string  $name
     * @param  mixed   $value
     * @return \D19sp\JWTAuth\Claims\Claim
     */
    public function get($name, $value)
    {
        if ($this->has($name)) {
            return new self::$classMap[$name]($value);
        }

        return new Custom($name, $value);
    }

    /**
     * Check whether the claim exists.
     *
     * @param  string  $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, self::$classMap);
    }
}
