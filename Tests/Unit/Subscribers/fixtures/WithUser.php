<?php

namespace Ambta\DoctrineEncryptBundle\Tests\Unit\Subscribers\fixtures;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use Doctrine\ORM\Mapping as ORM;

class WithUser
{
    public function __construct(
        #[Encrypted] public string $name,
        public string $foo,
        #[ORM\Embedded] public User $user,
    ) {}
}
