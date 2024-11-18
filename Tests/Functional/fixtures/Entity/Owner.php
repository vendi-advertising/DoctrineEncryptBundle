<?php


namespace Ambta\DoctrineEncryptBundle\Tests\Functional\fixtures\Entity;


use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Owner
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

    #[ORM\OneToOne(targetEntity: CascadeTarget::class, cascade: ['persist'])]
    private $cascaded;

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

    public function getCascaded(): mixed
    {
        return $this->cascaded;
    }

    public function setCascaded(mixed $cascaded): void
    {
        $this->cascaded = $cascaded;
    }
}