<?php
    
    /*
     * This file is part of the Symfony MakerBundle package.
     *
     * (c) Fabien Potencier <fabien@symfony.com>
     *
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */
    
    namespace NeoxMake\NeoxMakeBundle\Command;
    
    //use App\Controller\_CoreController;
    use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
    use Doctrine\Inflector\Inflector;
    use Doctrine\Inflector\InflectorFactory;
    use NeoxMake\NeoxMakeBundle\Utils\ValidatorCommand;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
    use Symfony\Bundle\MakerBundle\ConsoleStyle;
    use Symfony\Bundle\MakerBundle\DependencyBuilder;
    use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
    use Symfony\Bundle\MakerBundle\Generator;
    use Symfony\Bundle\MakerBundle\InputConfiguration;
    use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
    use Symfony\Bundle\MakerBundle\Renderer\FormTypeRenderer;
    use Symfony\Bundle\MakerBundle\Str;
    use Symfony\Bundle\TwigBundle\TwigBundle;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Question\Question;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Process\Process;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\Security\Csrf\CsrfTokenManager;
    use Symfony\Component\Validator\Validation;
    
    /**
     * @author Sadicov Vladimir <sadikoff@gmail.com>
     */
    final class MakeNeoxBundle extends AbstractMaker
    {
        private const BUNDLES_FILE_PATH     = 'config/bundles.php';
        private const COMPOSER_FILE_PATH    = 'composer.json';
        private const ORIGINAL_PATH         = 'vendor/xorgxx/neox-make-bundle/src/Resources/skeleton/';
        private bool $generateConfiguration = false;
        
        private string $original_path       = "vendor/xorgxx/neox-make-bundle/src/Resources/skeleton/";
        
        public function __construct(ParameterBagInterface $parameterBag)
        {
            $this->parameterBag = $parameterBag;
            $this->pathRepo     = $parameterBag->get('neox_make.directory_bundle');
            // Appel du constructeur parent avec le nom de la commande
//            parent::__construct('neoxmake:generate:bundle');
        }
        
        public static function getCommandName(): string
        {
            return 'neoxmake:generate:bundle';
        }
        
        public static function getCommandDescription(): string
        {
            return 'Creates reusable bundle skeleton generic for Symfony 🌭';
        }
        
        public function configureCommand(Command $command, InputConfiguration $inputConfig): void
        {
            $command
                ->addArgument('bundle-name', InputArgument::REQUIRED, 'Name bundle to create without [bundle] in end --> <fg=red>NeoxReusable !!</>')
                ->addOption('configure', '-c', InputOption::VALUE_NONE, 'Do you need to have configuration file ?')
                // the command help shown when running the command with the "--help" option
                ->setHelp(file_get_contents(__DIR__ . '/../Resources/help/MakeCrud.txt'));
            
            $inputConfig->setArgumentAsNonInteractive('entity-class');
        }
        
        public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
        {
            if (null === $input->getArgument('bundle-name')) {
                $argument = $command->getDefinition()->getArgument('bundle-name');
                $question = new Question($argument->getDescription());
                $value = $io->askQuestion($question);
                $input->setArgument('bundle-name', $value);
            }
            
            if (false === $input->getOption('configure')) {
                $argument = $command->getDefinition()->getOption('configure');
                $this->generateConfiguration = $io->confirm($argument->getDescription(), false);
//                $this->validatorCommand->checkBool($this->generateConfiguration);
            }
        }
        
        public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): int
        {
            $rootPath       = $this->pathRepo . $input->getArgument('bundle-name') ;
            $rootNameSpace  = $input->getArgument('bundle-name') . '\\' . $input->getArgument('bundle-name') . 'Bundle' ;
            
            # Bundle !!!
            $reusableBundle = [
                $input->getArgument('bundle-name'). 'Bundle' => [
                    $rootPath . '/src/' . $input->getArgument('bundle-name') . 'Bundle.php',
                    self::ORIGINAL_PATH . 'bundle/neoxBundle.tpl.php',
                    [
                        "name_space" => $rootNameSpace,
                        "class_name" => $input->getArgument('bundle-name') . 'Bundle',
                    ]
                ],
                'composer' => [
                    $rootPath . '/composer.json',
                    self::ORIGINAL_PATH . 'bundle/composer.json.tpl.php',
                    [
                        "name_space" => str_replace('/','\\\\', $rootNameSpace) . '\\\\',
                        "class_name" => $input->getArgument('bundle-name'),
                    ]
                ],
                'readme' => [
                    $rootPath . '/readme.md',
                    self::ORIGINAL_PATH . 'bundle/readme.md',
                    [
                        "name_space" => $rootNameSpace,
                        "class_name" => $input->getArgument('bundle-name'),
                    ]
                ],
                
                'LICENSE' => [
                    $rootPath . '/LICENSE',
                    self::ORIGINAL_PATH . 'bundle/LICENSE',
                    [
                        "name_space" => $rootNameSpace,
                        "class_name" => $input->getArgument('bundle-name'),
                    ]
                ],
                
                # DependencyInjection Folder !!!
                $input->getArgument('bundle-name') => [
                    $rootPath . '/src/DependencyInjection/' . $input->getArgument('bundle-name') . 'Extension.php',
                    self::ORIGINAL_PATH . 'bundle/DependencyInjection/NeoxBundleExtension.tpl.php',
                    [
                        "name_space" => $rootNameSpace. '\\DependencyInjection',
                        "class_name" => $input->getArgument('bundle-name') . 'Extension',
                    ]
                ],
                'configuration' => [
                    $rootPath . '/src/DependencyInjection/Configuration.php',
                    self::ORIGINAL_PATH . 'bundle/DependencyInjection/configuration.tpl.php',
                    [
                        "name_space" => $rootNameSpace . '\\DependencyInjection',
                        "class_name" => 'configuration',
                    ]
                ],
                
                # Resource Folder !!!
                "service.xml"=> [
                    $rootPath . '/src/Resources/config/services.xml',
                    self::ORIGINAL_PATH . 'bundle/Resources/config/services.tpl.xml',
                    [
                        "name_space" => $rootNameSpace . '\\Resources\\config',
                        "class_name" => $input->getArgument('bundle-name'),
                    ]
                ],
                'services.yml' => [
                    $rootPath . '/src/Resources/config/services.yaml',
                    self::ORIGINAL_PATH . 'bundle/Resources/config/services.tpl.yaml.php',
                    [
                        "name_space" => $rootNameSpace,
                    ]
                ],
            ];
            foreach ($reusableBundle as $class => $variables) {
                $generator->generateFile(
                    $variables[0],
                    $variables[1],
                    $variables[2]
                );
            }
            
            $generator->writeChanges();
            $this->writeSuccessMessage($io);
            $io->success('Dont forget to add in :');
            
            // NeoxMake\NeoxMakeBundle\NeoxMakeBundle::class => ['all' => true],
            $bundle = str_replace('/','\\', $rootNameSpace) . '\\' . $input->getArgument('bundle-name'). 'Bundle';
            $this->addBundle( $bundle, $io );
            
            // "NeoxNotifier\\NeoxtifierBundle\\": "Library/NeoxtifierBundle/src/",
            $composer = str_replace('\\','\\\\', $rootNameSpace) . '\\\\" : "' . $rootPath . '/src/"';
            $this->addComposer($composer, $io);
            
            $io->text(sprintf('Next: c dump-autoload & Check your new ReusableBundle by going to <fg=yellow>%s</>', $rootPath));
            return Command::SUCCESS;
        }
        
        public function configureDependencies(DependencyBuilder $dependencies): void
        {
            $dependencies->addClassDependency(
                Route::class,
                'router'
            );
            
            $dependencies->addClassDependency(
                AbstractType::class,
                'form'
            );
            
            $dependencies->addClassDependency(
                Validation::class,
                'validator'
            );
            
            $dependencies->addClassDependency(
                TwigBundle::class,
                'twig-bundle'
            );
            
            $dependencies->addClassDependency(
                DoctrineBundle::class,
                'orm'
            );
            
            $dependencies->addClassDependency(
                CsrfTokenManager::class,
                'security-csrf'
            );
            
            // !!!! remove the following dependencys for backwards compatibility with symfony < 6.2  compatibility
//        $dependencies->addClassDependency(
//            ParamConverter::class,
//            'annotations'
//        );
        }
        
        private function addBundle(string $bundleClass, $io): void
        {
            // Ajoutez votre bundle au tableau return dans config/bundles.php
            $content = file_get_contents(self::BUNDLES_FILE_PATH);
            
            // Trouvez la position du tableau return
            $returnPos = strpos($content, 'return [');
            
            // Si la position du tableau return est trouvée
            if ($returnPos !== false) {
                // Trouvez la position de la fin du tableau return
                $returnEndPos   = strpos($content, '];', $returnPos);
                
                // Ajoutez votre bundle à la fin du tableau return
                $tab = '    ';
                $newBundleLine  = sprintf("%s%s::class => ['all' => true],\n", $tab, $bundleClass);
                $content        = substr_replace($content, $newBundleLine, $returnEndPos, 0);
                
                // Enregistrez les modifications dans le fichier
                file_put_contents(self::BUNDLES_FILE_PATH, $content, LOCK_EX);
                
                $io->text(sprintf("Bundle <fg=yellow>%s</> was added successfully.", $bundleClass));
            } else {
                $io->text(sprintf("Unable to find return array in file <fg=yellow>%s</>.", $bundleClass));
            }
            
        }
        
        private function addComposer(string $composer, $io): void
        {
            // Ajoutez la configuration autoload PSR-4 au fichier composer.json
            $content = file_get_contents(self::COMPOSER_FILE_PATH);
            
            // Trouvez la position du "autoload" dans le fichier composer.json
            $autoloadPos = strpos($content, '"autoload"');
            $autoloadPos = strpos($content, '"psr-4"', $autoloadPos);
            
            // Si la position du "autoload" est trouvée
            if ($autoloadPos !== false) {
                // Trouvez la position de la fin du "autoload"
                $psr4EndPos = strpos($content, '{', $autoloadPos);
                
                // Ajoutez la configuration PSR-4 à la fin du "autoload"
                $psr4Config = "     \"$composer,";
                $content = substr_replace($content, "\n       $psr4Config", $psr4EndPos + 1, 0);
                
                // Enregistrez les modifications dans le fichier
                file_put_contents(self::COMPOSER_FILE_PATH, $content);
                
                $process = new Process(['composer', 'dump-autoload']);
                $process->run();
                
                $io->writeln("PSR-4 autoload configuration has been added successfully. The composer dump-autoload command has been validated.");
            } else {
                $io->writeln("Cannot find 'autoload' section in file" . self::COMPOSER_FILE_PATH);
            }
            
        }
    }