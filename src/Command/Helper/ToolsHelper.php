<?php
    
    namespace NeoxMake\NeoxMakeBundle\Command\Helper;
    
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Symfony\Component\Filesystem\Filesystem;
    use Symfony\Component\Finder\Finder;
    use Symfony\Component\Process\Process;
    use Symfony\Component\Serializer\Encoder\JsonEncoder;
    use Symfony\Component\Serializer\Serializer;
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
            $bundleNameOrig = str_replace('Bundle', "",$bundleNameOrig);
            $bundleName     = $bundleNameOrig . 'Bundle';
            
            return [
                // NeoxXorg
                "bundleNameOrig"        => $bundleNameOrig,
                // NeoxXorgBundle
                "bundleName"            => $bundleName,
                // neox_xorg
                "NameYaml"              => strtolower($this->camelCaseToSeparated( $bundleNameOrig, '_')),
                // neox-xorg-bundle
                "NameComposer"          => strtolower($this->camelCaseToSeparated( $bundleNameOrig, '-') . '-bundle'),
                // NeoxXorg\\NeoxXorgBundle\\
                "composerNameSpace"     => $bundleNameOrig . '\\' . $bundleName . '\\',
                // NeoxXorg\NeoxXorgBundle
                "rootNameSpace"         => $bundleNameOrig . '\\' . $bundleName,
                // reusableBundle/
                "pathRepo"              => $this->pathRepo,
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
        public function setMoveBundle(array $bundleBag, string $newLocation): bool
        {
            $path           = $bundleBag["pathRepo"] . $bundleBag["bundleName"];
            $filesystem     = new Filesystem();
            $filesystem->rename($path, $newLocation . "/{$bundleBag["bundleName"]}");
            return true;
        }
        
        
        public function setBundlePhp(array $bundleName, string $mode = "add") : bool
        {
            // xorgXxxx\xorgXxxxBundle\xorgXxxxBundle
            $nameSpaceBundle    = $bundleName["rootNameSpace"] . '\\' . $bundleName["bundleName"];
            $content            = file_get_contents(self::BUNDLES_FILE_PATH);
            
            if ($mode !== "add") {
                // neoxXorg\neoxXorgBundle\neoxXorgBundle::class => ['all' => true],
                $content            = str_replace("\t{$nameSpaceBundle}::class => ['all' => true],\n", "", $content);
            } else {
//                $bundleClass        = "{$bundleName_}\\{$bundleName}Bundle\\{$bundleName}Bundle";
                $returnPos          = strpos($content, 'return [');
                $returnEndPos       = strpos($content, '];', $returnPos);
                $newBundleLine      = sprintf("\t%s::class => ['all' => true],\n", $nameSpaceBundle);
                $content            = substr_replace($content, $newBundleLine, $returnEndPos, 0);
            }
            
            return file_put_contents(self::BUNDLES_FILE_PATH, $content, LOCK_EX);
        }
        
        public function setComposerJson(array $bundleBag, string $mode = "add") : bool
        {
            // xorgXxxx\xorgXxxxBundle\xorgXxxxBundle
            $nameSpaceBundle    = $bundleBag["rootNameSpace"]."\\";
            $content            = file_get_contents(self::COMPOSER_FILE_PATH);
            $jsonEncoder        = new JsonEncoder(defaultContext: ['json_encode_options' => JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE]);            $serializer         = new Serializer([], [$jsonEncoder]);
            $dataArray          = $serializer->decode($content, 'json');
            
            if ($mode !== "add") {
                if (isset($dataArray['autoload']['psr-4'][$nameSpaceBundle])) {
                    unset($dataArray['autoload']['psr-4']["$nameSpaceBundle"]);
                    if ($dataArray === false) {
                        throw new \RuntimeException('Erreur lors de l\'encodage JSON.');
                    }
                }
                if (isset($dataArray['autoload-dev']['psr-4'][$nameSpaceBundle.'Tests\\'])) {
                    unset($dataArray['autoload-dev']['psr-4'][$nameSpaceBundle.'Tests\\']);
                    if ($dataArray === false) {
                        throw new \RuntimeException('Erreur lors de l\'encodage JSON.');
                    }
                }
                
            } else {
                $directory  = "{$bundleBag["pathRepo"]}{$bundleBag["bundleName"]}/src/";
                if (!isset($dataArray['autoload']['psr-4'][$nameSpaceBundle])) {
                    $dataArray['autoload']['psr-4'][$nameSpaceBundle] = $directory;
                }
//                "neoxXorg\\neoxXorgBundle\\Tests\\": "reusableBundle/neox-xorg-bundle/tests/"
                if (!isset($dataArray['autoload-dev']['psr-4'][$nameSpaceBundle.'Tests\\'])) {
                    $dataArray['autoload-dev']['psr-4'][$nameSpaceBundle.'Tests\\'] = $directory;
                }
            }
            
//            $content    = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $content = $serializer->encode($dataArray, 'json');
            file_put_contents(self::COMPOSER_FILE_PATH, $content, LOCK_EX);
            
            $process = (new Process(['composer', 'dump-autoload']))->run();
            
            return true;
        }
        
        /**
         */
        public function setRemoveBundle(array $bundleBag): bool
        {   
            $path           = $bundleBag["pathRepo"] . $bundleBag["bundleName"]; 
            $filesystem     = new Filesystem();
            $filesystem->remove($path);
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