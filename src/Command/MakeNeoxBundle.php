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
        private Inflector $inflector;
        private bool $generateConfiguration = false;
        
        private const neox_table_crud_path = "neox/table";
        private ValidatorCommand $validatorCommand;
        
        private $original_path = "vendor/xorgxx/neox-make-bundle/src/Resources/skeleton/";
        
        public function __construct(ValidatorCommand $validatorCommand)
        {
            $this->validatorCommand = $validatorCommand;
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
                ->addArgument('bundle-name', InputArgument::REQUIRED, sprintf('Name bundle to create without [bundle] in end --> <fg=red>NeoxReusable !!</>', Str::asClassName(Str::getRandomTerm())))
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
            $rootPath       = 'Library/' . $input->getArgument('bundle-name') ;
            $rootNameSpace  = $input->getArgument('bundle-name') . '\\' . $input->getArgument('bundle-name') . 'Bundle' ;
            
            # Bundle !!!
            $reusableBundle = [
                $input->getArgument('bundle-name'). 'Bundle' => [
                    $rootPath . '/src/' . $input->getArgument('bundle-name') . 'Bundle.php',
                    $this->original_path . 'bundle/neoxBundle.tpl.php',
                    [
                        "name_space" => $rootNameSpace,
                        "class_name" => $input->getArgument('bundle-name') . 'Bundle',
                    ]
                ],
                'composer' => [
                    $rootPath . '/composer.json',
                    $this->original_path . 'bundle/composer.json.tpl.php',
                    [
                        "name_space" => str_replace('/','\\\\', $rootNameSpace) . '\\\\',
                        "class_name" => $input->getArgument('bundle-name'),
                    ]
                ],
                'readme' => [
                    $rootPath . '/readme.md',
                    $this->original_path . 'bundle/readme.md',
                    [
                        "name_space" => $rootNameSpace,
                        "class_name" => $input->getArgument('bundle-name'),
                    ]
                ],
                
                'LICENSE' => [
                    $rootPath . '/LICENSE',
                    $this->original_path . 'bundle/LICENSE',
                    [
                        "name_space" => $rootNameSpace,
                        "class_name" => $input->getArgument('bundle-name'),
                    ]
                ],
                
                # DependencyInjection Folder !!!
                $input->getArgument('bundle-name') => [
                    $rootPath . '/src/DependencyInjection/' . $input->getArgument('bundle-name') . 'Extension.php',
                    $this->original_path . 'bundle/DependencyInjection/NeoxBundleExtension.tpl.php',
                    [
                        "name_space" => $rootNameSpace. '\\DependencyInjection',
                        "class_name" => $input->getArgument('bundle-name') . 'Extension',
                    ]
                ],
                'configuration' => [
                    $rootPath . '/src/DependencyInjection/Configuration.php',
                    $this->original_path . 'bundle/DependencyInjection/configuration.tpl.php',
                    [
                        "name_space" => $rootNameSpace . '\\DependencyInjection',
                        "class_name" => 'configuration',
                    ]
                ],
                
                # Resource Folder !!!
                "service.xml"=> [
                    $rootPath . '/src/Resources/config/services.xml',
                    $this->original_path . 'bundle/Resources/config/services.tpl.xml',
                    [
                        "name_space" => $rootNameSpace . '\\Resources\\config',
                        "class_name" => $input->getArgument('bundle-name'),
                    ]
                ],
                'services.yml' => [
                    $rootPath . '/src/Resources/config/services.yaml',
                    $this->original_path . 'bundle/Resources/config/services.tpl.yaml.php',
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
//            NeoxMake\NeoxMakeBundle\NeoxMakeBundle::class => ['all' => true],
            $this->addBundle(str_replace('/','\\', $rootNameSpace) . '\\' . $input->getArgument('bundle-name'). 'Bundle', $io );
            
//            $bundles = str_replace('/','\\', $rootNameSpace) . '\\' . $input->getArgument('bundle-name') . "bundle::class => ['all' => true]";
            $io->success('Dont forget to add in :');
//            $io->text(sprintf('config/Bundles.php <fg=yellow>%s</>', $bundles));
//           "NeoxNotifier\\NeoxtifierBundle\\": "Library/NeoxtifierBundle/src/",
            $composer = str_replace('\\','\\\\', $rootNameSpace) . '\\\\" : "' . $rootPath . '/src/"';
            $this->addComposer($composer, $io);
//            $io->text(sprintf('composer.json -> autoload <fg=yellow>%s</>', $composer));

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
        
        private function addBundle(string $bundleClass, $io)
        {
            // Ajoutez votre bundle au tableau return dans config/bundles.php
            $bundlesFilePath    = 'config/bundles.php';
//            $bundleClass        = 'Mon\NomDeBundle\MonNomDeBundle';
            
            $content            = file_get_contents($bundlesFilePath);
            
            // Trouvez la position du tableau return
            $returnPos          = strpos($content, 'return [');
            
            // Si la position du tableau return est trouvée
            if ($returnPos !== false) {
                // Trouvez la position de la fin du tableau return
                $returnEndPos   = strpos($content, '];', $returnPos);
                $tab = '    ';
                // Ajoutez votre bundle à la fin du tableau return
                $content        = substr_replace($content, "{$tab}{$bundleClass}::class => ['all' => true],\n", $returnEndPos, 0);
                
                // Enregistrez les modifications dans le fichier
                file_put_contents($bundlesFilePath, $content, LOCK_EX);
                
                $io->text(sprintf("Le bundle <fg=yellow>%s</> a été ajouté avec succès.", $bundleClass));
//                $output->writeln("Le bundle a été ajouté avec succès.");
            } else {
                $io->text(sprintf("Impossible de trouver le tableau return dans le fichier <fg=yellow>%s</>.", $bundleClass));
//                $output->writeln("Impossible de trouver le tableau return dans le fichier {$bundlesFilePath}.");
            }
            
            return Command::SUCCESS;
        }
        
        private function addComposer(string $Composer, $io)
        {
            // Ajoutez la configuration autoload PSR-4 au fichier composer.json
            $composerFilePath       = 'composer.json';
            $psr4Config             = "     " . '"' . "$Composer";
            
            $content                = file_get_contents($composerFilePath);
            
            // Trouvez la position du "autoload" dans le fichier composer.json
            $autoloadPos            = strpos($content, '"autoload"');
            $autoloadPos            = strpos($content, '"psr-4"', $autoloadPos);
            // Si la position du "autoload" est trouvée
            if ($autoloadPos !== false) {
                // Trouvez la position de la fin du "autoload"
                $psr4EndPos = strpos($content, '{', $autoloadPos);
                /**
                 *  "autoload": {
                 *      "psr-4": {
                 *          "App\\": "src/",
                 *          "Dorg\\DorgBundle\\" : "Library/Dorg/src/"
                 *      }
                 *  },
                 */
                // Ajoutez la configuration PSR-4 à la fin du "autoload"
//                $content = substr_replace($content, "{$psr4Config}", $autoloadEndPos, 0);
                // Ajoutez une virgule à la fin de la ligne précédente dans le "psr-4"
                
                $content = substr_replace($content, "\n        {$psr4Config},", $psr4EndPos+1, 0);
                // Enregistrez les modifications dans le fichier
                file_put_contents($composerFilePath, $content);
                
                $process = new Process(['composer', 'dump-autoload']);
                $process->run();
                
                $io->writeln("La configuration autoload PSR-4 a été ajoutée avec succès. la commande composer dump-autoload validé");
            } else {
                $io->writeln("Impossible de trouver la section 'autoload' dans le fichier {$composerFilePath}.");
            }
            
            return Command::SUCCESS;
        }
    }