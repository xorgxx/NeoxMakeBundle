<?php
    namespace NeoxMake\NeoxMakeBundle\Controller;
    
    
    use NeoxMake\NeoxMakeBundle\Service\NeoxTableBuilder;
    use NeoxSeo\NeoxSeoBundle\Pattern\NeoxSeoFactory;
    use Symfony\Contracts\Translation\TranslatorInterface;
    
    trait Core
    {
        public function __construct(
            readonly TranslatorInterface $translator,
            readonly NeoxTableBuilder $neoxTableBuilder,
            readonly NeoxSeoFactory $seoFactory ) {
        }
    }