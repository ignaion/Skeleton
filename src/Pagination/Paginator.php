<?php

declare(strict_types=1);

namespace KejawenLab\Semart\Skeleton\Pagination;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use KejawenLab\Semart\Skeleton\AppEvent;
use KejawenLab\Semart\Skeleton\Setting\SettingService;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHLAK\Twine\Str;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Muhamad Surya Iksanudin <surya.iksanudin@gmail.com>
 */
class Paginator
{
    const PER_PAGE = 17;

    private $doctrine;

    private $paginator;

    private $request;

    private $eventDispatcher;

    private $settingService;

    public function __construct(
        ObjectManager $doctrine,
        PaginatorInterface $paginator,
        RequestStack $requestStack,
        EventDispatcherInterface $eventDispatcher,
        SettingService $settingService
    ) {
        $this->doctrine = $doctrine;
        $this->paginator = $paginator;
        $this->request = $requestStack->getCurrentRequest();
        $this->eventDispatcher = $eventDispatcher;
        $this->settingService = $settingService;
    }

    public function paginate(string $entityClass, int $page = 1, $orderField = null, $orderDirection = null): PaginationInterface
    {
        $limit = (int) $this->settingService->getValue('PER_PAGE') ?: self::PER_PAGE;
        $defaultOrderField = $orderField ?: $this->settingService->getValue('DEFAULT_ORDER');
        $defaultOrderDirection = $orderDirection ?: Str::make($this->settingService->getValue('DEFAULT_ORDER_DIRECTION'))->uppercase()->__toString();

        /** @var EntityRepository $repository */
        $repository = $this->doctrine->getRepository($entityClass);
        $queryBuilder = $repository->createQueryBuilder('o');

        $event = new FilterPagination();
        $event->setQueryBuilder($queryBuilder);
        $event->setRequest($this->request);
        $event->setEntityClass($entityClass);

        $this->eventDispatcher->dispatch(AppEvent::PAGINATION_EVENT, $event);

        if ($defaultOrderField) {
            $queryBuilder->addOrderBy(sprintf('o.%s', $defaultOrderField), $defaultOrderDirection ?: 'DESC');
        }

        return $this->paginator->paginate($queryBuilder, $page, $limit);
    }
}
