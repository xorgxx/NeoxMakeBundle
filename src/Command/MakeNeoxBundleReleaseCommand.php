<?php
    
    namespace NeoxMake\NeoxMakeBundle\Command;
    
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Question\ChoiceQuestion;
    use Symfony\Component\Console\Question\Question;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
    use Symfony\Component\Finder\Finder;
    use Symfony\Component\Process\Process;
    
    class MakeNeoxBundleReleaseCommand extends Command
    {
        private const BUNDLES_FILE_PATH     = 'config/bundles.php';
        private const COMPOSER_FILE_PATH    = 'composer.json';
        private string $pathRepo ;
        
        public function __construct(ParameterBagInterface $parameterBag)
        {
            $this->parameterBag = $parameterBag;
            $this->pathRepo     = $parameterBag->get('neox_make.directory_bundle');
            // Appel du constructeur parent avec le nom de la commande
            parent::__construct('neoxmake:bundle:release');
        }
        
        protected function configure()
        {
            $this->setName('neoxmake:bundle:release')
                ->setDescription('Relaese bundle to a new location.');
        }
        
        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $filesystem         = new Filesystem();
            $bundles            = $this->getBundles();
            // Demandez à l'utilisateur quel bundle doit être déplacé
            $question           = new ChoiceQuestion('Please choose the bundle you want to release:', $bundles);
            $question->setErrorMessage('Bundle %s does not exist.');
            $bundleName         = $this->getHelper('question')->ask($input, $output, $question);

            // Vérifiez si le bundle existe
//            if (!$this->bundleExists($bundleName)) {
//                $output->writeln("Le bundle {$bundleName} n'existe pas.");
//                return Command::FAILURE;
//            }
            
            // Demandez à l'utilisateur le nouvel emplacement du bundle
            $question       = new Question("Please enter the new location for the bundle {$bundleName}: [../Repo/]", '../Repo/');
            $newLocation    = $this->getHelper('question')->ask($input, $output, $question);
            
            // Déplacez le bundle vers le nouvel emplacement
            try {
                $filesystem->rename("$this->pathRepo{$bundleName}", $newLocation . "/{$bundleName}");
                $output->writeln("The bundle was successfully moved to {$newLocation}.");
            } catch (IOExceptionInterface $exception) {
                $output->writeln("Error moving bundle: {$exception->getMessage()}");
                return Command::FAILURE;
            }
            
            // Supprimez les références du bundle dans config/bundles.php
            // xorgXxxx\xorgXxxxBundle\xorgXxxxBundle
            $content        = file_get_contents(self::BUNDLES_FILE_PATH);
            $bundleClass    = "{$bundleName}\\{$bundleName}Bundle\\{$bundleName}Bundle";
            $content        = str_replace("{$bundleClass}::class => ['all' => true],\n", '', $content);
            file_put_contents(self::BUNDLES_FILE_PATH, $content, LOCK_EX);
            
            // Supprimez les références du bundle dans composer.json
            // "xorgXxxx\\xorgXxxxBundle\\" : "Library/xorgXxxx/src/",
            $composerContent    = file_get_contents(self::COMPOSER_FILE_PATH);
            $composerClass      = "{$bundleName}\\\\{$bundleName}Bundle\\\\";
            $composerContent    = str_replace("\"{$composerClass}\" : \"$this->pathRepo{$bundleName}/src/\",", '', $composerContent);
            file_put_contents(self::COMPOSER_FILE_PATH, $composerContent, LOCK_EX);
            
            // Exécutez la commande composer dump-autoload
            $process = new Process(['composer', 'dump-autoload']);
            $process->run();
            
            $output->writeln("The bundle references have been successfully released.");
            
            return Command::SUCCESS;
        }
        
        private function deleteLine($cheminFichier, $ligneASupprimer) {
            // Lire le contenu du fichier dans un tableau
            $contenuFichier = file($cheminFichier);
            
            // Rechercher la ligne à supprimer
            $indexLigneASupprimer = array_search($ligneASupprimer, $contenuFichier);
            
            // Si la ligne est trouvée, la supprimer
            if ($indexLigneASupprimer !== false) {
                unset($contenuFichier[$indexLigneASupprimer]);
            }
            
            // Réécrire le contenu dans le fichier
            file_put_contents($cheminFichier, implode("", $contenuFichier));
        }
        
        private function bundleExists($bundleName)
        {
            return is_dir("$this->pathRepo{$bundleName}");
        }
        
        
        private function getBundles()
        {
            $bundles = [];
            
            // Utilisez le composant Finder pour parcourir les dossiers dans le répertoire "Library"
            $finder = new Finder();
            $finder->directories()->in($this->pathRepo)->depth(0);
            
            // Ajoutez chaque dossier trouvé comme un possible bundle
            foreach ($finder as $directory) {
                $bundles[] = $directory->getBasename();
            }
            
            return $bundles;
        }
    }