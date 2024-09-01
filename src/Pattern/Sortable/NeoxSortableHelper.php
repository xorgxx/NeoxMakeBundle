<?php

namespace NeoxMake\NeoxMakeBundle\Pattern\Sortable;

use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

class NeoxSortableHelper
{
    
    private ?array $Headers = [];
//    private TranslatorInterface $translator;
//
//    public function __construct(TranslatorInterface $translator)
//    {
//        $this->translator = $translator;
//    }

    

    public function getConfigYaml( $pathYaml ): void
    {
        $this->config  =  Yaml::parseFile( $pathYaml, Yaml::PARSE_OBJECT_FOR_MAP);
        $y = $this->config->iniSortable->headers;
        foreach ($y as $key => $value) {
            $this->Headers[] = $value->key;
        }

    }
    
    public function getHeaders(): array
    {
        return $this->Headers;
    }
}