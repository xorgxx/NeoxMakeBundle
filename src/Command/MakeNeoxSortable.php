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
    use NeoxMake\NeoxMakeBundle\Controller\_NeoxCoreController;
    use NeoxMake\NeoxMakeBundle\Service\NeoxTableBuilder;
    use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
    use Doctrine\Inflector\Inflector;
    use Doctrine\Inflector\InflectorFactory;
    use Doctrine\ORM\EntityManagerInterface;
    use Doctrine\ORM\EntityRepository;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Bundle\FrameworkBundle\KernelBrowser;
    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Bundle\MakerBundle\ConsoleStyle;
    use Symfony\Bundle\MakerBundle\DependencyBuilder;
    use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
    use Symfony\Bundle\MakerBundle\Generator;
    use Symfony\Bundle\MakerBundle\InputConfiguration;
    use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
    use Symfony\Bundle\MakerBundle\Renderer\FormTypeRenderer;
    use Symfony\Bundle\MakerBundle\Str;
    use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
    use Symfony\Bundle\MakerBundle\Validator;
    use Symfony\Bundle\TwigBundle\TwigBundle;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Question\Question;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\Security\Csrf\CsrfTokenManager;
    use Symfony\Component\Validator\Validation;

    /**
     * @author Sadicov Vladimir <sadikoff@gmail.com>
     */
    final class MakeNeoxSortable extends AbstractMaker
    {
        private Inflector $inflector;
        private string    $controllerClassName;
        private bool      $generateTests      = false;
        private bool      $generateTranslator = false;

        private const neox_table_crud_path    = "neox/table";
        private const neox_Resources_skeleton = "vendor/xorgxx/neox-make-bundle/src/Resources/skeleton/";

        public function __construct(private DoctrineHelper $doctrineHelper, private FormTypeRenderer $formTypeRenderer)
        {
            $this->inflector = InflectorFactory::create()->build();
        }

        public static function getCommandName(): string
        {
            return 'neoxmake:sortable:entity';
        }

        public static function getCommandDescription(): string
        {
            return 'Creates NeoxTable Sortable 🌭 CRUD for Doctrine entity class';
        }

        public function configureCommand(Command $command, InputConfiguration $inputConfig): void
        {
            $command
                ->addArgument('entity-class', InputArgument::OPTIONAL, sprintf('The class name of the entity to create --> <fg=red>NeoxSortable Entity !!</> <-- CRUD (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
                ->setHelp(file_get_contents(__DIR__ . '/../Resources/help/MakeCrud.txt'));

            $inputConfig->setArgumentAsNonInteractive('entity-class');
        }

        public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
        {
            if (null === $input->getArgument('entity-class')) {
                $argument = $command->getDefinition()->getArgument('entity-class');

                $entities = $this->doctrineHelper->getEntitiesForAutocomplete();

                $question = new Question($argument->getDescription());
                $question->setAutocompleterValues($entities);

                $value = $io->askQuestion($question);

                $input->setArgument('entity-class', $value);
            }
        }

        public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
        {
            // keeping entity details just to use system builtin skeleton to check repository!!!
            list($entityClassDetails, $entityDoctrineDetails, $repositoryVars,
                $repositoryClassName, $controllerClassDetails, $formClassDetails)
                = $this->getEntityDetails($generator, $input);

            $entityTwigVarSingular = Str::asTwigVariable($input->getArgument('entity-class'));

            // create Component generic
            $t = 'src/Twig/Components/yaml/' . $entityTwigVarSingular . 'Sortable.test';
            $generator->generateFile(
                $t,
                self::neox_Resources_skeleton . 'sortable/Twig/Components/sortable.tpl.yaml',
            );

            $generator->writeChanges();

            $this->writeSuccessMessage($io);

            $io->text(sprintf('Next: Check how to use by going to <fg=yellow>%s/</>', $t));

        }

        public
        function configureDependencies(DependencyBuilder $dependencies): void
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

        /**
         * @param Generator      $generator
         * @param InputInterface $input
         *
         * @return array
         */
        private
        function getEntityDetails(Generator $generator, InputInterface $input): array
        {
            // get entity details
            $entityClassDetails    = $generator->createClassNameDetails(
                Validator::entityExists($input->getArgument('entity-class'), $this->doctrineHelper->getEntitiesForAutocomplete()),
                'Entity\\'
            );
            $entityDoctrineDetails = $this->doctrineHelper->createDoctrineDetails($entityClassDetails->getFullName());
            $repositoryVars        = [];
            $repositoryClassName   = EntityManagerInterface::class;
            if (null !== $entityDoctrineDetails->getRepositoryClass()) {
                $repositoryClassDetails = $generator->createClassNameDetails(
                    '\\' . $entityDoctrineDetails->getRepositoryClass(),
                    'Repository\\',
                    'Repository'
                );

                $repositoryClassName = $repositoryClassDetails->getFullName();

                $repositoryVars = [
                    'repository_full_class_name' => $repositoryClassName,
                    'repository_class_name'      => $repositoryClassDetails->getShortName(),
                    'repository_var'             => lcfirst($this->inflector->singularize($repositoryClassDetails->getShortName())),
                ];
            }
            $controllerClassDetails = null;
            $formClassDetails       = null;
            return array( $entityClassDetails, $entityDoctrineDetails, $repositoryVars, $repositoryClassName, $controllerClassDetails, $formClassDetails );
        }
    }