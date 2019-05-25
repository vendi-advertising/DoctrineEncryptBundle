<?php

/*
 * This file is part of the DoctrineEncryptBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ambta\DoctrineEncryptBundle;

use Ambta\DoctrineEncryptBundle\DependencyInjection\DoctrineEncryptExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AmbtaDoctrineEncryptBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new DoctrineEncryptExtension();
    }
}
