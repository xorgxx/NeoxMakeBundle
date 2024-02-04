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
    use NeoxMake\NeoxMakeBundle\Command\Helper\ToolsHelper;
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
    use Symfony\Component\Filesystem\Filesystem;
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
        private const ORIGINAL_PATH         = 'vendor/xorgxx/neox-make-bundle/src/Resources/skeleton/';
        private bool $generateConfiguration = false;
        private string $original_path       = "vendor/xorgxx/neox-make-bundle/src/Resources/skeleton/";
        public ToolsHelper $toolsHelper;
        
        public function __construct( ToolsHelper $toolsHelper )
        {
            $this->toolsHelper  = $toolsHelper;
            $this->pathRepo     = $this->toolsHelper->pathRepo; 
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

            $bundleBag = $this->toolsHelper->getBundleNameConvert($input->getArgument('bundle-name'));
            
            # Folder !!!
            $io->success($this->toolsHelper->setFolderRepo());
            
            # Bundle !!!
            $reusableBundle = [
                $input->getArgument('bundle-name'). 'Bundle' => [
                    $bundleBag["rootPath"] . '/src/' . $input->getArgument('bundle-name') . 'Bundle.php',
                    self::ORIGINAL_PATH . 'bundle/neoxBundle.tpl.php',
                    [
                        "name_space" => $bundleBag["rootNameSpace"],
                        "class_name" => $input->getArgument('bundle-name') . 'Bundle',
                    ]
                ],
                'composer' => [
                    $bundleBag["rootPath"] . '/composer.json',
                    self::ORIGINAL_PATH . 'bundle/composer.json.tpl.php',
                    [
                        "name_space" => str_replace('/','\\\\', $bundleBag["rootNameSpace"]) . '\\\\',
                        "class_name" => $input->getArgument('bundle-name'),
                    ]
                ],
                'readme' => [
                    $bundleBag["rootPath"] . '/readme.md',
                    self::ORIGINAL_PATH . 'bundle/readme.md',
                    [
                        "name_space" => $bundleBag["rootNameSpace"],
                        "class_name" => $input->getArgument('bundle-name'),
                    ]
                ],
                
                'LICENSE' => [
                    $bundleBag["rootPath"] . '/LICENSE',
                    self::ORIGINAL_PATH . 'bundle/LICENSE',
                    [
                        "name_space" => $bundleBag["rootNameSpace"],
                        "class_name" => $input->getArgument('bundle-name'),
                    ]
                ],
                
                # DependencyInjection Folder !!!
                
                $input->getArgument('bundle-name') => [
                    $bundleBag["rootPath"] . '/src/DependencyInjection/' . $input->getArgument('bundle-name') . 'Extension.php',
                    self::ORIGINAL_PATH . 'bundle/DependencyInjection/NeoxBundleExtension.tpl.php',
                    [
                        "config_yaml"   => $bundleBag["configName"],
                        "name_space"    => $bundleBag["rootNameSpace"]. '\\DependencyInjection',
                        "class_name"    => $input->getArgument('bundle-name') . 'Extension',
                    ]
                ],
                'configuration' => [
                    $bundleBag["rootPath"] . '/src/DependencyInjection/Configuration.php',
                    self::ORIGINAL_PATH . 'bundle/DependencyInjection/configuration.tpl.php',
                    [
                        "config_yaml"   => $bundleBag["configName"],
                        "name_space"    => $bundleBag["rootNameSpace"] . '\\DependencyInjection',
                        "class_name"    => 'configuration',
                    ]
                ],
                
                # Resource Folder !!!
                "service.xml"=> [
                    $bundleBag["rootPath"] . '/src/Resources/config/services.xml',
                    self::ORIGINAL_PATH . 'bundle/Resources/config/services.tpl.xml',
                    [
                        "name_space" => $bundleBag["rootNameSpace"] . '\\Resources\\config',
                        "class_name" => $input->getArgument('bundle-name'),
                    ]
                ],
                'services.yml' => [
                    $bundleBag["rootPath"] . '/src/Resources/config/services.yaml',
                    self::ORIGINAL_PATH . 'bundle/Resources/config/services.tpl.yaml.php',
                    [
                        "name_space" => $bundleBag["rootNameSpace"],
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
            
            $this->toolsHelper->setBundlePhp($input->getArgument('bundle-name'));
            
            $this->toolsHelper->setComposerJson( $bundleBag );
            
            $io->text(sprintf('Next: c dump-autoload & Check your new ReusableBundle by going to <fg=yellow>%s</>', $bundleBag["rootPath"]));
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
        
    }