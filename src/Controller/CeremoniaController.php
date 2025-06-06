<?php

namespace App\Controller;

use App\Entity\Ceremonia;
use App\Repository\CeremoniaRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ceremonias')]
class CeremoniaController extends AbstractController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    #[Route('', name: 'ceremonias_list', methods: ['GET'])]
    public function getCeremonias(Request $request, CeremoniaRepository $ceremoniaRepository): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $categoria = $request->query->get('categoria');
        $search = $request->query->get('search');

        if ($search) {
            $ceremonias = $ceremoniaRepository->search($search);
            $result = array_map(fn($ceremonia) => $ceremonia->toArray(), $ceremonias);
        } elseif ($categoria) {
            $ceremonias = $ceremoniaRepository->findByCategoria($categoria);
            $result = array_map(fn($ceremonia) => $ceremonia->toArray(), $ceremonias);
        } else {
            // Return grouped by category
            $grouped = $ceremoniaRepository->findGroupedByCategory();
            $result = [];
            foreach ($grouped as $cat => $ceremonias) {
                $result[$cat] = array_map(fn($ceremonia) => $ceremonia->toArray(), $ceremonias);
            }
        }

        return new JsonResponse($result);
    }

    #[Route('/{id}', name: 'ceremonia_detail', methods: ['GET'])]
    public function getCeremonia(Request $request, int $id, CeremoniaRepository $ceremoniaRepository): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $ceremonia = $ceremoniaRepository->find($id);
        if (!$ceremonia) {
            return new JsonResponse(['error' => 'Ceremonia no encontrada'], 404);
        }

        return new JsonResponse($ceremonia->toArray());
    }

    #[Route('/nombre/{nombre}', name: 'ceremonia_by_name', methods: ['GET'])]
    public function getCeremoniaByName(Request $request, string $nombre, CeremoniaRepository $ceremoniaRepository): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $ceremonia = $ceremoniaRepository->findByNombre($nombre);
        if (!$ceremonia) {
            return new JsonResponse(['error' => 'Ceremonia no encontrada'], 404);
        }

        return new JsonResponse($ceremonia->toArray());
    }

    #[Route('', name: 'ceremonia_create', methods: ['POST'])]
    public function createCeremonia(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        // Only allow certain roles to create ceremonias
        if (!in_array($user->getRole(), ['babalawo', 'admin'])) {
            return new JsonResponse(['error' => 'No autorizado para crear ceremonias'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['nombre']) || !isset($data['descripcion']) || !isset($data['categoria'])) {
            return new JsonResponse(['error' => 'Nombre, descripción y categoría son requeridos'], 400);
        }

        $ceremonia = new Ceremonia();
        $ceremonia->setNombre($data['nombre']);
        $ceremonia->setDescripcion($data['descripcion']);
        $ceremonia->setCategoria($data['categoria']);
        
        if (isset($data['requisitos'])) {
            $ceremonia->setRequisitos($data['requisitos']);
        }
        if (isset($data['materiales'])) {
            $ceremonia->setMateriales($data['materiales']);
        }
        if (isset($data['procedimiento'])) {
            $ceremonia->setProcedimiento($data['procedimiento']);
        }
        if (isset($data['duracion_minutos'])) {
            $ceremonia->setDuracionMinutos($data['duracion_minutos']);
        }

        try {
            $entityManager->persist($ceremonia);
            $entityManager->flush();

            return new JsonResponse($ceremonia->toArray(), 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error al crear ceremonia: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'ceremonia_update', methods: ['PUT'])]
    public function updateCeremonia(Request $request, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        // Only allow certain roles to update ceremonias
        if (!in_array($user->getRole(), ['babalawo', 'admin'])) {
            return new JsonResponse(['error' => 'No autorizado para editar ceremonias'], 403);
        }

        $ceremonia = $entityManager->getRepository(Ceremonia::class)->find($id);
        if (!$ceremonia) {
            return new JsonResponse(['error' => 'Ceremonia no encontrada'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['nombre'])) {
            $ceremonia->setNombre($data['nombre']);
        }
        if (isset($data['descripcion'])) {
            $ceremonia->setDescripcion($data['descripcion']);
        }
        if (isset($data['categoria'])) {
            $ceremonia->setCategoria($data['categoria']);
        }
        if (isset($data['requisitos'])) {
            $ceremonia->setRequisitos($data['requisitos']);
        }
        if (isset($data['materiales'])) {
            $ceremonia->setMateriales($data['materiales']);
        }
        if (isset($data['procedimiento'])) {
            $ceremonia->setProcedimiento($data['procedimiento']);
        }
        if (isset($data['duracion_minutos'])) {
            $ceremonia->setDuracionMinutos($data['duracion_minutos']);
        }

        $ceremonia->setUpdatedAt(new \DateTime());

        try {
            $entityManager->flush();
            return new JsonResponse($ceremonia->toArray());
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error al actualizar ceremonia: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/basicas', name: 'ceremonias_basicas', methods: ['GET'])]
    public function getCeremoniasBasicas(Request $request, CeremoniaRepository $ceremoniaRepository): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $ceremonias = $ceremoniaRepository->findBasicas();
        $result = array_map(fn($ceremonia) => $ceremonia->toArray(), $ceremonias);

        return new JsonResponse($result);
    }

    #[Route('/avanzadas', name: 'ceremonias_avanzadas', methods: ['GET'])]
    public function getCeremoniasAvanzadas(Request $request, CeremoniaRepository $ceremoniaRepository): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $ceremonias = $ceremoniaRepository->findAvanzadas();
        $result = array_map(fn($ceremonia) => $ceremonia->toArray(), $ceremonias);

        return new JsonResponse($result);
    }

    #[Route('/popular', name: 'ceremonias_popular', methods: ['GET'])]
    public function getMostPopularCeremonias(Request $request, CeremoniaRepository $ceremoniaRepository): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $limit = min(20, max(5, (int)$request->query->get('limit', 10)));
        $ceremonias = $ceremoniaRepository->findMostPopular($limit);

        $result = array_map(fn($ceremonia) => $ceremonia->toArray(), $ceremonias);

        return new JsonResponse($result);
    }
} 