<?php

namespace App\Controller\Api\V1;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Service\Attribute\Required;

class UserRestController extends AbstractController
{
    #[Required]
    public EntityManagerInterface $entityManager;
    #[Required]
    public SerializerInterface $serializer;
    #[Required]
    public Security $security;

    #[Route('/users', name: 'user_create', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = $request->request->all();

        if (!$data) {
            return new JsonResponse(['error' => 'No data']);
        }

        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->submit($data);

        if (!$form->isValid()) {
            return new JsonResponse(['errors' => $this->getFormErrors($form)], Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $serializedData = $this->serializer->serialize($user, 'json');

        return new JsonResponse($serializedData, Response::HTTP_CREATED, [], true);
    }

    #[Route('/users/{id}', name: 'user_update', methods: ['PUT'])]
    public function updateUser(int $id, Request $request): JsonResponse
    {
        $data = $request->request->all();

        if (!$data) {
            return new JsonResponse(['error' => 'No data']);
        }

        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(UserType::class, $user);
        $form->submit($data, false);

        if (!$form->isValid()) {
            return new JsonResponse(['errors' => $this->getFormErrors($form)], Response::HTTP_BAD_REQUEST);
        }

        $serializedData = $this->serializer->serialize($user, 'json', ['groups' => ['user:edit']]);

        return new JsonResponse($serializedData, Response::HTTP_OK, [], true);
    }

    #[Route('/users/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function deleteUser(int $id): JsonResponse
    {
        $currentUser = $this->security->getUser();

        if ($currentUser?->getUserIdentifier() !== 'testAdmin') {
            throw new AccessDeniedException();
        }

        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse([], Response::HTTP_OK);
    }

    #[Route('/users', name: 'user_list', methods: ['GET'])]
    public function showUsers(): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $users = $this->entityManager->getRepository(User::class)->findAll();

        return new JsonResponse($this->serializer->serialize($users, 'json', ['groups' => ['user:read']]), Response::HTTP_OK, [], true);
    }

    #[Route('/users/{id}', name: 'user_show', methods: ['GET'])]
    public function showUser(int $id): JsonResponse
    {
        $currentUser = $this->security->getUser();

        if (!$currentUser) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if ($this->isGranted('ROLE_USER') && $currentUser->getUserIdentifier() !== 'testUser') {
            throw new AccessDeniedException();
        }

        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializer->serialize($user, 'json'), Response::HTTP_OK, [], true);
    }


    // TODO: в ідеалі винести в сервіс або базовий контроллер
    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors(true) as $error) {
            $errors[$error->getOrigin()->getName()] = $error->getMessage();
        }

        return $errors;
    }
}
