<?php

namespace Ambta\DoctrineEncryptBundle\Tests\Unit\Subscribers\fixtures;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;

class ExtendedUser extends User
{
    public function __construct(
        string $name,
        ?string $address,
        #[Encrypted] public ?string $extra,
    ) {
        parent::__construct($name, $address);
    }
}
