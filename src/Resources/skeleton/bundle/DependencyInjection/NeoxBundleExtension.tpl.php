<?= "<?php\n" ?>

	namespace <?= $name_space; ?>;

	use Exception;
	use Symfony\Component\Config\FileLocator;
	use Symfony\Component\DependencyInjection\ContainerBuilder;
	use Symfony\Component\DependencyInjection\Extension\Extension;
	use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
    use Symfony\Component\DependencyInjection\Reference;

    class <?= $class_name ?> extends Extension
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
<?php if ($config): ?>
            foreach ($config as $key => $value) {
                $container->setParameter( '<?= $config_yaml; ?>.' . $key, $value);
            }
<?php else: ?>
            //foreach ($config as $key => $value) {
            //    $container->setParameter( '<?= $config_yaml; ?>.' . $key, $value);
            //}
<?php endif ?>
        
		}
	}