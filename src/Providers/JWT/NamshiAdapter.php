<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Sean D19sp <dinho19sp@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace D19sp\JWTAuth\Providers\JWT;

use Exception;
use Namshi\JOSE\JWS;
use D19sp\JWTAuth\Exceptions\JWTException;
use D19sp\JWTAuth\Exceptions\TokenInvalidException;

class NamshiAdapter extends JWTProvider implements JWTInterface
{
    /**
     * @var \Namshi\JOSE\JWS
     */
    protected $jws;

    /**
     * @param string  $secret
     * @param string  $algo
     * @param null    $driver
     */
    public function __construct($secret, $algo, $driver = null)
    {
        parent::__construct($secret, $algo);

        $this->jws = $driver ?: new JWS(['typ' => 'JWT', 'alg' => $algo]);
    }

    /**
     * Create a JSON Web Token.
     *
     * @return string
     * @throws \D19sp\JWTAuth\Exceptions\JWTException
     */
    public function encode(array $payload)
    {
        try {
            $this->jws->setPayload($payload)->sign($this->secret);

            return $this->jws->getTokenString();
        } catch (Exception $e) {
            throw new JWTException('Could not create token: '.$e->getMessage());
        }
    }

    /**
     * Decode a JSON Web Token.
     *
     * @param  string  $token
     * @return array
     * @throws \D19sp\JWTAuth\Exceptions\JWTException
     */
    public function decode($token)
    {
        try {
            $jws = JWS::load($token);
        } catch (Exception $e) {
            throw new TokenInvalidException('Could not decode token: '.$e->getMessage());
        }

        if (! $jws->verify($this->secret, $this->algo)) {
            throw new TokenInvalidException('Token Signature could not be verified.');
        }

        return $jws->getPayload();
    }
}
