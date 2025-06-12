<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    private AuthService $authService;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    public function __construct(
        AuthService $authService,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ) {
        $this->authService = $authService;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    #[Route('/users/pending', name: 'admin_users_pending', methods: ['GET'])]
    public function getPendingUsers(Request $request): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        if (!in_array($user->getRole(), ['admin', 'babalawo'])) {
            return new JsonResponse(['error' => 'No autorizado'], 403);
        }

        $pendingUsers = $this->userRepository->findBy(['status' => 'pending'], ['created_at' => 'DESC']);
        
        $result = array_map(function($user) {
            $userData = $user->toArray();
            // Don't include sensitive data in lists
            unset($userData['password_hash']);
            return $userData;
        }, $pendingUsers);

        return new JsonResponse($result);
    }

    #[Route('/users/{uuid}', name: 'admin_users_detail', methods: ['GET'])]
    public function getUserDetail(Request $request, string $uuid): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        if (!in_array($user->getRole(), ['admin', 'babalawo'])) {
            return new JsonResponse(['error' => 'No autorizado'], 403);
        }

        $user = $this->userRepository->findOneByUuid(['uuid' => $uuid]);
        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        return new JsonResponse($user->toArray());
    }

    #[Route('/users/approved', name: 'admin_users_approved', methods: ['GET'])]
    public function getApprovedUsers(Request $request): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        if (!in_array($user->getRole(), ['admin', 'babalawo'])) {
            return new JsonResponse(['error' => 'No autorizado'], 403);
        }

        $approvedUsers = $this->userRepository->findBy(['status' => 'approved'], ['created_at' => 'DESC']);
        
        $result = array_map(function($user) {
            $userData = $user->toArray();
            // Don't include sensitive data in lists
            unset($userData['password_hash']);
            return $userData;
        }, $approvedUsers);

        return new JsonResponse($result);
    }

    #[Route('/users/{uuid}/approve', name: 'admin_user_approve', methods: ['POST'])]
    public function approveUser(Request $request, string $uuid): JsonResponse
    {
        try {
            $currentUser = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        if (!in_array($currentUser->getRole(), ['admin', 'babalawo'])) {
            return new JsonResponse(['error' => 'No autorizado'], 403);
        }

        $user = $this->userRepository->findOneByUuid(['uuid' => $uuid]);
        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        if ($user->getStatus() !== 'pending') {
            return new JsonResponse(['error' => 'El usuario no está pendiente de aprobación'], 400);
        }

        $user->setStatus('approved');
        $user->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Usuario aprobado exitosamente',
            'user' => $user->toArray()
        ]);
    }

    #[Route('/users/{uuid}/reject', name: 'admin_user_reject', methods: ['POST'])]
    public function rejectUser(Request $request, string $uuid): JsonResponse
    {
        try {
            $currentUser = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        if (!in_array($currentUser->getRole(), ['admin', 'babalawo'])) {
            return new JsonResponse(['error' => 'No autorizado'], 403);
        }

        $user = $this->userRepository->findOneByUuid(['uuid' => $uuid]);
        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        if ($user->getStatus() !== 'pending') {
            return new JsonResponse(['error' => 'El usuario no está pendiente de aprobación'], 400);
        }

        $data = json_decode($request->getContent(), true);
        $reason = $data['reason'] ?? null;

        $user->setStatus('rejected');
        if ($reason) {
            $user->setNotes(($user->getNotes() ? $user->getNotes() . "\n\n" : '') . "Rechazado: " . $reason);
        }
        $user->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Usuario rechazado',
            'user' => $user->toArray()
        ]);
    }

    #[Route('/users/{uuid}/suspend', name: 'admin_user_suspend', methods: ['POST'])]
    public function suspendUser(Request $request, string $uuid): JsonResponse
    {
        try {
            $currentUser = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        if (!in_array($currentUser->getRole(), ['admin', 'babalawo'])) {
            return new JsonResponse(['error' => 'No autorizado'], 403);
        }

        $user = $this->userRepository->findOneByUuid(['uuid' => $uuid]);
        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        if ($user->getStatus() === 'suspended') {
            return new JsonResponse(['error' => 'El usuario ya está suspendido'], 400);
        }

        $data = json_decode($request->getContent(), true);
        $reason = $data['reason'] ?? null;

        $user->setStatus('suspended');
        if ($reason) {
            $user->setNotes(($user->getNotes() ? $user->getNotes() . "\n\n" : '') . "Suspendido: " . $reason);
        }
        $user->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Usuario suspendido',
            'user' => $user->toArray()
        ]);
    }

    #[Route('/users/{uuid}/reactivate', name: 'admin_user_reactivate', methods: ['POST'])]
    public function reactivateUser(Request $request, string $uuid): JsonResponse
    {
        try {
            $currentUser = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        if (!in_array($currentUser->getRole(), ['admin', 'babalawo'])) {
            return new JsonResponse(['error' => 'No autorizado'], 403);
        }

        $user = $this->userRepository->findOneByUuid(['uuid' => $uuid]);
        if (!$user) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        if ($user->getStatus() === 'approved') {
            return new JsonResponse(['error' => 'El usuario ya está activo'], 400);
        }

        $user->setStatus('approved');
        $user->setNotes(($user->getNotes() ? $user->getNotes() . "\n\n" : '') . "Reactivado el " . date('Y-m-d H:i:s'));
        $user->setUpdatedAt(new \DateTime());

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Usuario reactivado exitosamente',
            'user' => $user->toArray()
        ]);
    }

    #[Route('/users', name: 'admin_users_list', methods: ['GET'])]
    public function getAllUsers(Request $request): JsonResponse
    {
        try {
            $currentUser = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        if (!in_array($currentUser->getRole(), ['admin', 'babalawo'])) {
            return new JsonResponse(['error' => 'No autorizado'], 403);
        }

        $status = $request->query->get('status');
        $role = $request->query->get('role');
        
        $criteria = [];
        if ($status) {
            $criteria['status'] = $status;
        }
        if ($role) {
            $criteria['role'] = $role;
        }

        $users = $this->userRepository->findBy($criteria, ['created_at' => 'DESC']);
        
        $result = array_map(function($user) {
            $userData = $user->toArray();
            // Don't include sensitive data in lists
            unset($userData['password_hash']);
            return $userData;
        }, $users);

        return new JsonResponse($result);
    }

    #[Route('/stats', name: 'admin_stats', methods: ['GET'])]
    public function getStats(Request $request): JsonResponse
    {
        try {
            $currentUser = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        if (!in_array($currentUser->getRole(), ['admin', 'babalawo'])) {
            return new JsonResponse(['error' => 'No autorizado'], 403);
        }

        $stats = [
            'users' => [
                'total' => $this->userRepository->count([]),
                'pending' => $this->userRepository->count(['status' => 'pending']),
                'approved' => $this->userRepository->count(['status' => 'approved']),
                'suspended' => $this->userRepository->count(['status' => 'suspended']),
                'rejected' => $this->userRepository->count(['status' => 'rejected'])
            ],
            'roles' => [
                'babalawo' => $this->userRepository->count(['role' => 'babalawo']),
                'santero' => $this->userRepository->count(['role' => 'santero']),
                'iyanifa' => $this->userRepository->count(['role' => 'iyanifa']),
                'aleyo' => $this->userRepository->count(['role' => 'aleyo']),
                'admin' => $this->userRepository->count(['role' => 'admin'])
            ]
        ];

        return new JsonResponse($stats);
    }
} 