<?php

/*
 * This file is part of the SymfonyCasts ResetPasswordBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeoxMake\NeoxMakeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('neox_make');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('path_js_bs-datatable')
                    ->defaultValue("build/canvas/js/components/bs-datatable.js")
                    ->info('Path to JS file bs-datatable.js. (bootstrap5)')
                ->end()
                ->scalarNode('path_css_bs-datatable')
                    ->defaultValue("build/canvas/css/components/bs-datatable.css")
                    ->info('Path to CSS file bs-datatable.css. (bootstrap5)')
                ->end()
        ;

        return $treeBuilder;
    }
}