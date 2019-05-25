<?php

/*
 * This file is part of the DoctrineEncryptBundle package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ambta\DoctrineEncryptBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration tree for security bundle. Full tree you can see in Resources/docs.
 *
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        // Create tree builder
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ambta_doctrine_encrypt');

        // Grammar of config tree
        $rootNode
                ->children()
                    ->scalarNode('encryptor_class')
                        ->defaultValue('Halite')
                    ->end()
                    ->scalarNode('secret_directory_path')
                        ->defaultValue('%kernel.project_dir%')
                    ->end()
                ->end();

        return $treeBuilder;
    }
}
