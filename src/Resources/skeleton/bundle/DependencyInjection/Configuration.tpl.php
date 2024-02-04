<?= "<?php\n" ?>



/*
 * This file is part of the SymfonyCasts ResetPasswordBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace <?= $name_space; ?>;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author xorg <xorg@i2p.i2p>
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('<?= $config_yaml; ?>');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('directory_bundle')->defaultValue("Library/")->end()
            ->end()
        ;

        return $treeBuilder;
    }
}