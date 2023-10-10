<?php

namespace NeoxTable\NeoxTableBundle\Twig;

use DateTime;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
//    private TranslatorInterface    $translator;
//    private RouterInterface        $router;
//    private EntityManagerInterface $entityManager;

    /**
     * @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;
    /**
     * @var Environment
     */
    private Environment $twig;

    public function __construct(ParameterBagInterface $parameterBag, Environment $twig)
    {
        // Inject dependencies if needed

        $this->parameterBag = $parameterBag;
        $this->twig         = $twig;
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
//            new TwigFunction('Address', array($this, 'Address')),

        ];
    }


    /**
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\LoaderError
     */
    public function setJsFile(): string
    {
        $y = $this->parameterBag->get("neox_table.path_js_bs-datatable");
        return $this->twig->render("@NeoxTable/_neoxTableJs.html.twig",["pathJs" => $y ]);
    }
}