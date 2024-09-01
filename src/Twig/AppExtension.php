<?php

namespace NeoxMake\NeoxMakeBundle\Twig;

use DateTime;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use NeoxMake\NeoxMakeBundle\Service\ReflectionHelper;

class AppExtension extends AbstractExtension
{
    private TranslatorInterface    $translator;
    private RouterInterface        $router;
    private EntityManagerInterface $entityManager;


    public function __construct(ParameterBagInterface $parameterBag, Environment $twig, TranslatorInterface $translator)
    {
        $this->parameterBag = $parameterBag;
        $this->twig         = $twig;
        $this->translator   = $translator;
    }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('setJsFile', [$this, 'setJsFile']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('HeaderTitle', array($this, 'HeaderTitle'), array(
                'is_safe'           => array('html'),
                'needs_environment' => true,
            )),
            new TwigFunction('setJsFile', [$this, 'setJsFile']),
            new TwigFunction('getPropertyType', [ReflectionHelper::class, 'getPropertyType']),
            new TwigFunction('getTrans', [$this, 'getTranslation']),

        ];
    }

    /**
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\LoaderError
     */
    public function setJsFile(): string
    {
        $y = $this->parameterBag->get("neox_make.table.path_js_bs-datatable");
        return $this->twig->render("@NeoxMake/_neoxTableJs.html.twig",["pathJs" => $y ]);
    }
    
    public function getTranslation(array $initial)
    {
        if (array_key_exists('key', $initial )) {
            $domain = strstr($initial['trans'], '.', true) ?: $initial['key'];
            return $this->translator->trans($initial['trans'], [], $domain);
        } elseif (!empty($initial['label'])) {
            $domain = strstr($initial['label'], '.', true) ?: $initial['label'];
            return $this->translator->trans($initial['label'], [], $domain);
        } else {
            return 'nc';
        }

//        if (!empty($initial['label'])) {
//            $domain = strstr($initial['label'], '.', true) ?: $initial['label'];
//            return $this->translator->trans($initial['label'], [], $domain);
//        }
//        return $initial['label'] ?? $initial['key'];
    }
}