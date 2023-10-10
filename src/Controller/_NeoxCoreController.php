<?php
    
    namespace NeoxMake\NeoxMakeBundle\Controller;

    use NeoxMake\NeoxMakeBundle\Service\NeoxTableBuilder;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Contracts\Translation\TranslatorInterface;

    /**
     *
     */
    class _NeoxCoreController extends AbstractController
    {

		public TranslatorInterface $translator;

        /**
         * @var NeoxTableBuilder
         */
        private NeoxTableBuilder $neoxTableBuilder;

        /**
         * @param TranslatorInterface $translator
         * @param NeoxTableBuilder $neoxTableBuilder
         */
        public function __construct( TranslatorInterface $translator, NeoxTableBuilder $neoxTableBuilder ) {
            $this->translator       = $translator;
            $this->neoxTableBuilder = $neoxTableBuilder;
        }

        /**
         * @return NeoxTableBuilder
         */
        public function getNeoxTableBuilder(): NeoxTableBuilder
        {
            return (new $this->neoxTableBuilder($this->translator));
        }

        /**
         * @return TranslatorInterface
         */
        public function getTranslator() : TranslatorInterface {
            return $this->translator;
        }

    }