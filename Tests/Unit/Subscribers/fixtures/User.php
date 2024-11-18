<?php

namespace Ambta\DoctrineEncryptBundle\Tests\Unit\Subscribers\fixtures;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;

class User
{
    public function __construct(
        #[Encrypted] public string $name,
        #[Encrypted] private ?string $address,
    ) {}

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }
}
