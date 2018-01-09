<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Sean D19sp <dinho19sp@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace D19sp\JWTAuth\Validators;

use D19sp\JWTAuth\Exceptions\JWTException;

abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * @var bool
     */
    protected $refreshFlow = false;

    /**
     * Helper function to return a boolean.
     *
     * @param  array  $value
     * @return bool
     */
    public function isValid($value)
    {
        try {
            $this->check($value);
        } catch (JWTException $e) {
            return false;
        }

        return true;
    }

    /**
     * Set the refresh flow flag.
     *
     * @param  bool  $refreshFlow
     * @return $this
     */
    public function setRefreshFlow($refreshFlow = true)
    {
        $this->refreshFlow = $refreshFlow;

        return $this;
    }
}
