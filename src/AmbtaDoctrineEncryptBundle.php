<?php

namespace Ambta\DoctrineEncryptBundle;

use JetBrains\PhpStorm\Pure;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Ambta\DoctrineEncryptBundle\DependencyInjection\DoctrineEncryptExtension;

class AmbtaDoctrineEncryptBundle extends Bundle
{
    #[Pure]
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new DoctrineEncryptExtension();
    }
}
