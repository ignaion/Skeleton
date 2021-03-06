<?php

declare(strict_types=1);

namespace KejawenLab\Semart\Skeleton\Controller\Admin;

use KejawenLab\Semart\Skeleton\Entity\Group;
use KejawenLab\Semart\Skeleton\Pagination\Paginator;
use KejawenLab\Semart\Skeleton\Repository\GroupRepository;
use KejawenLab\Semart\Skeleton\Request\RequestHandler;
use KejawenLab\Semart\Skeleton\Security\Authorization\Permission;
use KejawenLab\Semart\Skeleton\Security\Service\SuperAdministratorCheckerService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/groups")
 *
 * @Permission(menu="GROUP")
 *
 * @author Muhamad Surya Iksanudin <surya.iksanudin@gmail.com>
 */
class GroupController extends AdminController
{
    /**
     * @Route("/", methods={"GET"}, name="groups_index", options={"expose"=true})
     *
     * @Permission(actions=Permission::VIEW)
     */
    public function index(Request $request, Paginator $paginator)
    {
        $groups = $paginator->paginate(Group::class, (int) $request->query->get('page', 1));

        if ($request->isXmlHttpRequest()) {
            $table = $this->renderView('group/table-content.html.twig', ['groups' => $groups]);
            $pagination = $this->renderView('group/pagination.html.twig', ['groups' => $groups]);

            return new JsonResponse([
                'table' => $table,
                'pagination' => $pagination,
            ]);
        }

        return $this->render('group/index.html.twig', ['title' => 'Grup', 'groups' => $groups]);
    }

    /**
     * @Route("/{id}", methods={"GET"}, name="groups_detail", options={"expose"=true})
     *
     * @Permission(actions=Permission::VIEW)
     */
    public function find(string $id, GroupRepository $repository, SerializerInterface $serializer)
    {
        $group = $repository->find($id);
        if (!$group) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($serializer->serialize($group, 'json', ['groups' => ['read']]));
    }

    /**
     * @Route("/save", methods={"POST"}, name="groups_save", options={"expose"=true})
     *
     * @Permission(actions={Permission::ADD, Permission::EDIT})
     */
    public function save(Request $request, GroupRepository $repository, RequestHandler $requestHandler)
    {
        $primary = $request->get('id');
        if ($primary) {
            $group = $repository->find($primary);
        } else {
            $group = new Group();
        }

        $requestHandler->handle($request, $group);
        if (!$requestHandler->isValid()) {
            return new JsonResponse(['status' => 'KO', 'errors' => $requestHandler->getErrors()]);
        }

        $this->commit($group);

        return new JsonResponse(['status' => 'OK']);
    }

    /**
     * @Route("/{id}/delete", methods={"POST"}, name="groups_remove", options={"expose"=true})
     *
     * @Permission(actions=Permission::DELETE)
     */
    public function delete(string $id, GroupRepository $repository)
    {
        if (!$group = $repository->find($id)) {
            throw new NotFoundHttpException();
        }

        $this->remove($group);

        return new JsonResponse(['status' => 'OK']);
    }

    /**
     * @Route("/{id}/restore", methods={"POST"}, name="groups_restore", options={"expose"=true})
     *
     * @Permission(actions=Permission::DELETE)
     */
    public function restore(string $id, GroupRepository $repository, SuperAdministratorCheckerService $service)
    {
        if (!$city = $repository->find($id) && $service->isAdmin()) {
            throw new NotFoundHttpException();
        }

        $repository->restore($id);

        return new JsonResponse(['status' => 'OK']);
    }
}
