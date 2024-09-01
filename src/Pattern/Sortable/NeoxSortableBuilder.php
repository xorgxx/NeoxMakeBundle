<?php

    namespace NeoxMake\NeoxMakeBundle\Pattern\Sortable;

    use NeoxMake\NeoxMakeBundle\Service\ReflectionHelper;
    use Symfony\Component\Yaml\Yaml;
    use Symfony\UX\Turbo\TurboBundle;
    use App\Model\Headers;
    use Symfony\Component\HttpKernel\KernelInterface;

    class NeoxSortableBuilder
    {
        public ?array $headers = null;
        public ?array $actions = null;
        public ?array $initial = null;
        public mixed  $config  = null;      # WELL! yes mmm ok i know :-> what fuck 🦖

        public function __construct(KernelInterface $kernel, string $class)
        {
            $this->projectDir = $kernel->getProjectDir();
            $this->getConfigYaml($class);

        }

        public function getConfigYaml($class): void
        {
            $path         = ReflectionHelper::getPathIni($class, $this->projectDir);
            $this->config = Yaml::parseFile($path, Yaml::PARSE_OBJECT_FOR_MAP);

            // set headers
            $this->setHeaders();

            // set headers
            $this->setActions();

            // set initial
            $this->setInitial();
        }

        public function getHeaders(): ?array
        {
            if (!is_array($this->Headers) || empty($this->Headers)) {
                throw new \InvalidArgumentException('Headers are empty or not an array');
            }
            return $this->Headers;
        }

        public function setHeaders(): self
        {
            $this->Headers = json_decode(json_encode($this->config->iniSortable->table), true);
            return $this;
        }

        public function getActions(): ?array
        {
            return $this->actions;
        }

        public function setActions(): self
        {
            $this->actions[ "item" ]   = json_decode(json_encode($this->config->iniSortable->actions->item), true);
            $this->actions[ "header" ] = json_decode(json_encode($this->config->iniSortable->actions->header), true);
            return $this;
        }

        public function getInitial(): ?array
        {
            return $this->initial;
        }

        public function setInitial(): self
        {
            $this->initial = json_decode(json_encode($this->config->iniSortable->initial), true);
            $this->setConfig();
            return $this;
        }

        public function getConfig(): ?array
        {
            return $this->config;
        }

        public function setConfig(): void
        {
            $this->config = json_decode(json_encode($this->config->iniSortable->config), true);
        }


    }