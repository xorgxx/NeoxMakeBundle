<?php
    
    namespace NeoxMake\NeoxMakeBundle\Command\Helper;
    
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\Finder\Finder;
    use Symfony\Component\Process\Process;
    use Composer\Json\JsonFile;
    use Symfony\Component\Serializer\Encoder\JsonDecode;
    
    class ToolsHelper
    {
        private const BUNDLES_FILE_PATH     = 'config/bundles.php';
        private const COMPOSER_FILE_PATH    = 'composer.json';
        public string $pathRepo ;
        
        public function __construct(ParameterBagInterface $parameterBag)
        {
            $this->parameterBag     = $parameterBag;
            $this->pathRepo         = $parameterBag->get('neox_make.directory_bundle');
            
        }
        
        public function getBundleNameConvert(string $bundleNameOrig): array
        {
            $bundleName    = strtolower($this->camelCaseToSeparated( $bundleNameOrig . 'Bundle', '-'));
            
            return [
                "bundleNameOrig" => $bundleNameOrig,
                "bundleName"     => $bundleName,
                "configName"     => strtolower($this->camelCaseToSeparated( $bundleNameOrig, '_')),
                "rootPath"       => $this->pathRepo . $bundleName,
                "rootNameSpace"  => $bundleNameOrig . '\\' . $bundleNameOrig . 'Bundle',
                "pathRepo"       => $this->pathRepo,
                "dirComposer"    => $this->pathRepo . strtolower($this->camelCaseToSeparated( $bundleNameOrig, '-')),
            ];
 
        }
        
        public function setFolderRepo(): string
        {
            $filesystem     = new Filesystem();
            $r              = "The {$this->pathRepo} folder already exists.";
            if (!$filesystem->exists($this->pathRepo)) {
                // Si le dossier n'existe pas, créez-le
                $filesystem->mkdir($this->pathRepo);
               $r = "The {$this->pathRepo} folder was created successfully.";
            }
            
            return $r;
        }
        public function getBundles()
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
        
        /**
         */
        public function setMoveBundle(string $bundleName, string $newLocation): bool
        {
            $filesystem  = new Filesystem();
            $filesystem->rename("$this->pathRepo{$bundleName}", $newLocation . "/{$bundleName}");
            return true;
        }
        
        public function setBundlePhp(string $bundleName, string $mode = "add") : bool
        {
            // xorgXxxx\xorgXxxxBundle\xorgXxxxBundle
            
            $bundleNameSnake    = $this->toCamelCase( $bundleName );
            $bundleName_        = str_replace("Bundle", "", $bundleNameSnake);
            $content            = file_get_contents(self::BUNDLES_FILE_PATH);
            
            if ($mode !== "add") {
                // neoxXorg\neoxXorgBundle\neoxXorgBundle::class => ['all' => true],
                $bundleClass        = "{$bundleName_}\\{$bundleNameSnake}\\{$bundleNameSnake}";
                $content            = str_replace("{$bundleClass}::class => ['all' => true],\n", '', $content);
            } else {
                $bundleClass        = "{$bundleName_}\\{$bundleNameSnake}Bundle\\{$bundleNameSnake}Bundle";
                $returnPos          = strpos($content, 'return [');
                $returnEndPos       = strpos($content, '];', $returnPos);
                $tab                = '    ';
                $newBundleLine      = sprintf("%s%s::class => ['all' => true],\n", $tab, $bundleClass);
                $content            = substr_replace($content, $newBundleLine, $returnEndPos, 0);
            }

            return file_put_contents(self::BUNDLES_FILE_PATH, $content, LOCK_EX);
        }
        
        public function setComposerJson(array $bundleBag, string $mode = "add") : bool
        {
            // xorgXxxx\xorgXxxxBundle\xorgXxxxBundle
            $bundleNameSnake    = $this->toCamelCase( $bundleBag["bundleName"] );
            $bundleName_        = str_replace("Bundle", "", $bundleNameSnake);
            $content            = file_get_contents(self::COMPOSER_FILE_PATH);
            $composerClass      = "{$bundleName_}\\\\{$bundleNameSnake}\\\\";
            
            if ($mode !== "add") {
                $bundleNameSnake    = $this->toCamelCase( $bundleBag["bundleNameOrig"] );
                $composerClass      = "{$bundleName_}\\\\{$bundleNameSnake}\\\\";
                $content            = str_replace("\"{$composerClass}\" : \"{$bundleBag['dirComposer']}/src/\",", '', $content);
            } else {
                $autoloadPos        = strpos($content, '"autoload"');
                $autoloadPos        = strpos($content, '"psr-4"', $autoloadPos);
                $psr4EndPos         = strpos($content, '{', $autoloadPos);
                $composer           = str_replace('\\','\\\\', $bundleBag["rootNameSpace"]) . '\\\\" : "' . $bundleBag["rootPath"] . '/src/"';
                $psr4Config         = "     \"$composer,";
                $content            = substr_replace($content, "\n       $psr4Config", $psr4EndPos + 1, 0);
            }
            
            file_put_contents(self::COMPOSER_FILE_PATH, $content, LOCK_EX);
            
            $process = (new Process(['composer', 'dump-autoload']))->run();
            
            return true;
        }
        
        /**
         */
        public function setRemoveBundle(string $bundleName): bool
        {
            $filesystem  = new Filesystem();
            $filesystem->remove($bundleName);
            return true;
        }
        
        /**
         * @param $input
         * @param $separator
         *  // Ex:
         *  $camelCaseText = "exampleText";
         *  $snakeCaseText = camelCaseToSeparated($camelCaseText, '_');
         *  $kebabCaseText = camelCaseToSeparated($camelCaseText, '-');
         *
         * @return array|string|string[]|null
         */
        private function camelCaseToSeparated($input, $separator = '_') {
            return preg_replace('/(?!^)[[:upper:]]/', $separator . '$0', $input);
        }
        
        /**
         * @param $input
         * @param $separator
         *
         * @return string
         */
        private function toCamelCase($input, $separator = '-')
        {
            return lcfirst(str_replace($separator, '', ucwords($input, $separator)));
        }
    }