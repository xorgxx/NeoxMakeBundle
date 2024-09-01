<?php
    
    namespace NeoxMake\NeoxMakeBundle\Command;
    
    use NeoxMake\NeoxMakeBundle\Command\Helper\ToolsHelper;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Question\ChoiceQuestion;
    use Symfony\Component\Console\Question\Question;
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
    use Symfony\Component\Finder\Finder;
    use Symfony\Component\Process\Process;
    
    class MakeNeoxBundleReleaseCommand extends Command
    {
        private string $pathRepo ;
        public ToolsHelper $toolsHelper;
        
        public function __construct(ToolsHelper $toolsHelper)
        {
            $this->toolsHelper  = $toolsHelper;
            $this->pathRepo     = $this->toolsHelper->pathRepo; //$parameterBag->get('neox_make.directory_bundle');
            
            // Appel du constructeur parent avec le nom de la commande
            parent::__construct('neoxmake:bundle:release');
        }
        
        protected function configure()
        {
            $this->setName('neoxmake:bundle:release')
                ->setDescription('Relaese bundle to a new location.');
        }
        
        protected function execute(InputInterface $input, OutputInterface $output):int
        {
            
            $bundles            = $this->toolsHelper->getBundles();
            
            // Demandez à l'utilisateur quel bundle doit être déplacé
            $question           = new ChoiceQuestion('Please choose the bundle you want to release:', $bundles);
            $question->setErrorMessage('Bundle %s does not exist.');
            $bundleName         = $this->getHelper('question')->ask($input, $output, $question);
            $bundleBag          = $this->toolsHelper->getBundleNameConvert($bundleName);
            // ask action
            $question           = new ChoiceQuestion('Select action :', ["Delete", "Release"]);
            $action             = $this->getHelper('question')->ask($input, $output, $question);
            
            try {
                if ($action === "Release") {
                    // Ask the user for the new location of the bundle
                    // Move the bundle to the new location0
                    $question       = new Question("Please enter the new location for the bundle {$bundleName}: [../Repo/]", '../Repo/');
                    $newLocation    = $this->getHelper('question')->ask($input, $output, $question);
                    $this->toolsHelper->setMoveBundle($bundleBag, $newLocation);
                    $output->writeln("The bundle | {$bundleBag["bundleName"]} was successfully moved to {$newLocation}.");
                }
                if ($action === "Delete") {
                    $this->toolsHelper->setRemoveBundle($bundleBag);
                    $output->writeln("The bundle | {$bundleBag["bundleName"]} was successfully removed.");
                }
              
            } catch (IOExceptionInterface $exception) {
                $output->writeln("Error moving bundle: {$exception->getMessage()}");
                return Command::FAILURE;
            }
            
            // Remove bundle references in config/bundles.php
            // xorgXxxx\xorgXxxxBundle\xorgXxxxBundle
            $this->toolsHelper->setBundlePhp($bundleBag, "release");
            
            // Remove bundle references in composer.json
            // "xorgXxxx\\xorgXxxxBundle\\" : "Library/xorgXxxx/src/",
            $this->toolsHelper->setComposerJson($bundleBag, "release");
            
            $output->writeln("The bundle | {$bundleBag["bundleName"]} references have been successfully $action.");
            
            return Command::SUCCESS;
        }
        
    }