<?php

	namespace NeoxMake\NeoxMakeBundle\DependencyInjection;

	use Exception;
	use Symfony\Component\Config\FileLocator;
	use Symfony\Component\DependencyInjection\ContainerBuilder;
	use Symfony\Component\DependencyInjection\Extension\Extension;
	use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
    use Symfony\Component\DependencyInjection\Reference;
    use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

    class NeoxMakeExtension extends Extension implements PrependExtensionInterface
	{

		/**
		 * @inheritDoc
		 * @throws Exception
		 */
		public function load( array $configs, ContainerBuilder $container ) :void
		{

			$loader         = new YamlFileLoader( $container, new FileLocator(__DIR__ . "/../Resources/config") );
			$loader->load("services.yaml");

            $configuration  = $this->getConfiguration($configs, $container);
            $config         = $this->processConfiguration($configuration, $configs);
            
            // set key config as container parameters
            foreach ($config as $key => $value) {
                $container->setParameter('neox_make.'.$key, $value);
            }
		}

        public function prepend(ContainerBuilder $container)
        {
            $container->prependExtensionConfig('twig', [
                'paths' => [
                    '%kernel.project_dir%/vendor/xorgxx/neox-make-bundle/src/templates' => 'NeoxMakeBundle',
                ],
            ]);
//            $container->prependExtensionConfig('twig_component', [
//                'defaults' => [
//                    '%kernel.project_dir%/vendor/xorgxx/neox-make-bundle/src/Twig/Components\\' => '~',
//                ],
//            ]);
        }
	}