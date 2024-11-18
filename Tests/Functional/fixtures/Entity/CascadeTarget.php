<?php

namespace Ambta\DoctrineEncryptBundle\Tests\Functional\fixtures\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class CascadeTarget
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[Encrypted]
    #[ORM\Column(type: 'string', nullable: true)]
    private mixed $secret;

    #[ORM\Column(type: 'string', nullable: true)]
    private mixed $notSecret;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSecret(): mixed
    {
        return $this->secret;
    }

    public function setSecret(mixed $secret): void
    {
        $this->secret = $secret;
    }

    public function getNotSecret(): mixed
    {
        return $this->notSecret;
    }

    public function setNotSecret(mixed $notSecret): void
    {
        $this->notSecret = $notSecret;
    }
}