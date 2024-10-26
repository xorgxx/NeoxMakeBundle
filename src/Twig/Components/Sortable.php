<?php

    namespace NeoxMake\NeoxMakeBundle\Twig\Components;

    use App\Model\Headers;
    use NeoxMake\NeoxMakeBundle\Services\ReflectionHelper;
    use Doctrine\ORM\EntityManagerInterface;
    use Exception;
    use NeoxMake\NeoxMakeBundle\Pattern\Sortable\NeoxSortableBuilder;
    use ReflectionClass;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpKernel\KernelInterface;
    use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
    use Symfony\UX\LiveComponent\Attribute\LiveAction;
    use Symfony\UX\LiveComponent\Attribute\LiveArg;
    use Symfony\UX\LiveComponent\Attribute\LiveProp;
    use Symfony\UX\LiveComponent\DefaultActionTrait;

    #[AsLiveComponent('Sortable', template: '@NeoxMakeBundle/Components/Sortable.html.twig')]
    final class Sortable extends abstractController
    {
        use DefaultActionTrait;

        #[LiveProp]
        public ?array $initial  = null;
        #[LiveProp]
        public ?array $config  = null;
        #[LiveProp]
        public ?string $class    = null;
        #[LiveProp]
        public ?array  $headers  = null;
        #[LiveProp]
        public ?string $cSort    = null;
        #[LiveProp]
        public ?string $cHeader  = null;
        #[LiveProp(writable: true)]
        public ?string $query    = null;
        public ?int    $nbItems  = 0;
        public ?array  $entities = null;
        #[LiveProp]
        public ?array  $actions  = null;


        public function __construct(private readonly EntityManagerInterface $entityManager, private readonly KernelInterface $kernel,){}

        public function mount(string $class): void
        {
            // entity/post
            $NeoxSortableBuilder = new NeoxSortableBuilder($this->kernel, $class);
            $this->headers       = $NeoxSortableBuilder->getHeaders();
            $this->actions       = $NeoxSortableBuilder->getActions();
            $this->initial       = $NeoxSortableBuilder->getInitial();
            $this->config        = $NeoxSortableBuilder->getConfig();
            $this->cHeader       = $this->headers[ 0 ][ "key" ];
            $this->class         = $class;

            $this->sort($this->cHeader);
        }

        #[LiveAction]
        public function sortable(#[LiveArg] string $header): void
        {
            $this->sort($header);
        }

        #[LiveAction]
        public function search(): void
        {
            $this->sort($this->cHeader); // Reapply sorting after search
        }

        private function sort(string $header): void
        {
            $repository    = $this->entityManager->getRepository($this->class);
            $this->cHeader = $header;
            $this->cSort   = $this->cSort === 'asc' ? 'desc' : 'asc';
            $this->nbItems = $repository->count();
            $qb            = $repository->createQueryBuilder('e')
                ->select('COUNT(e)')
                ->orderBy('e.' . $this->cHeader, $this->cSort);

            if ($this->query) {
                $orX = $qb->expr()->orX(...array_map(function ($header) use ($qb) {
                        return $qb->expr()->like('e.' . $header[ "key" ], ':searchTerm');
                    }, $this->headers)
                );

                $qb->where($orX);
                $qb->setParameter('searchTerm', '%' . $this->query . '%');
            }

            $qb->select('e');
            $this->entities = $qb->getQuery()->getResult();
        }


    }
