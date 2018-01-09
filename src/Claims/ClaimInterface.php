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

interface ClaimInterface
{
    /**
     * Set the claim value, and call a validate method if available.
     *
     * @param mixed
     * @return Claim
     */
    public function setValue($value);

    /**
     * Get the claim value.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set the claim name.
     *
     * @param string  $name
     * @return Claim
     */
    public function setName($name);

    /**
     * Get the claim name.
     *
     * @return string
     */
    public function getName();
}
