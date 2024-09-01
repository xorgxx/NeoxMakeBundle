<?php

    namespace NeoxMake\NeoxMakeBundle\Service;

    use ReflectionClass;

    class ReflectionHelper
    {
//    "D:/Developpement/Web/PhpstormProjects/berlin/src/Twig/Components/yaml/";
        const PATH_INI = "/src/Twig/Components/yaml/";

        public static function getPropertyType(object $object, string $property): ?string
        {
            try {
                $reflectionClass = new \ReflectionClass($object);
                if ($reflectionClass->hasProperty($property)) {
                    $reflectionProperty = $reflectionClass->getProperty($property);
                    $type               = $reflectionProperty->getType();

                    if ($type !== null) {
                        return $type->getName();
                    }
                }
            } catch (\ReflectionException $e) {

                return null;

            }

            return null;
        }

        public static function getPathIni(string $class, string $path = null): ?string
        {
            try {
                // entity/post
                $reflectionClass = new ReflectionClass($class);
                $shortClassName  = $reflectionClass->getShortName();
//
                return $path . self::PATH_INI . $shortClassName . 'Sortable.yaml';

            } catch (\ReflectionException $e) {

                return null;
            }
        }
    }