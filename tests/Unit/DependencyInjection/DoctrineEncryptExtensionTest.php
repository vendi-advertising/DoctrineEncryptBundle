<?php

/*
 * This file is part of the DoctrineEncryptBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ambta\DoctrineEncryptBundle\Tests\Unit\DependencyInjection;

use Ambta\DoctrineEncryptBundle\DependencyInjection\DoctrineEncryptExtension;
use Ambta\DoctrineEncryptBundle\Encryptors\DefuseEncryptor;
use Ambta\DoctrineEncryptBundle\Encryptors\HaliteEncryptor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class DoctrineEncryptExtensionTest extends TestCase
{
    /**
     * @var DoctrineEncryptExtension
     */
    private $extension;

    protected function setUp()
    {
        $this->extension = new DoctrineEncryptExtension();
    }

    public function testConfigLoadHalite()
    {
        $container = $this->createContainer();
        $this->extension->load([[]], $container);

        $this->assertSame(HaliteEncryptor::class, $container->getParameter('ambta_doctrine_encrypt.encryptor_class_name'));
    }

    public function testConfigLoadDefuse()
    {
        $container = $this->createContainer();

        $config = [
            'encryptor_class' => 'Defuse',
        ];
        $this->extension->load([$config], $container);

        $this->assertSame(DefuseEncryptor::class, $container->getParameter('ambta_doctrine_encrypt.encryptor_class_name'));
    }

    public function testConfigLoadCustom()
    {
        $container = $this->createContainer();
        $config = [
            'encryptor_class' => self::class,
        ];
        $this->extension->load([$config], $container);

        $this->markTestSkipped();

        $this->assertSame(self::class, $container->getParameter('ambta_doctrine_encrypt.encryptor_class_name'));
    }

    private function createContainer()
    {
        $container = new ContainerBuilder(
            new ParameterBag(['kernel.debug' => false])
        );

        return $container;
    }
}
