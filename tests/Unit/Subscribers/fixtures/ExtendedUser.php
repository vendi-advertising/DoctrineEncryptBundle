<?php

/*
 * This file is part of the DoctrineEncryptBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ambta\DoctrineEncryptBundle\Tests\Unit\Subscribers\fixtures;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;

class ExtendedUser extends User
{
    /**
     * @var string
     * @Encrypted()
     */
    public $extra;

    public function __construct(string $name, ?string $address, ?string $extra)
    {
        parent::__construct($name, $address);
        $this->extra = $extra;
    }
}
