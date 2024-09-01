<?php

    namespace NeoxMake\NeoxMakeBundle\Pattern;

    use Symfony\Contracts\Translation\TranslatorInterface;

    class TranslationHelper
    {
        /**
         * @var TranslatorInterface|null
         */
        private static $translator = null;

        /**
         * Initializes the TranslatorInterface.
         *
         * @param TranslatorInterface $translator
         */
        public static function init(TranslatorInterface $translator): TranslatorInterface
        {
            self::$translator = $translator;
            return self::$translator;
        }

        /**
         * Translates a given key using the initialized TranslatorInterface.
         *
         * @param string $key The translation key
         * @param array $parameters Optional parameters to replace in the translation string
         * @param string|null $locale The locale to use (null for the default locale)
         *
         * @return string The translated string
         *
         * @throws \LogicException if the translator is not initialized
         */
        public static function translate(string $key, array $parameters = [], ?string $locale = null): string
        {
            if (self::$translator === null) {
                throw new \LogicException('Translator not initialized. Call TranslationHelper::init() first.');
            }

            return self::$translator->trans($key, $parameters, null, $locale);
        }
    }
