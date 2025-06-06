<?php

namespace App\Controller;

use App\Entity\Orisha;
use App\Repository\OrishaRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/orishas')]
class OrishaController extends AbstractController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    #[Route('', name: 'orishas_list', methods: ['GET'])]
    public function getOrishas(Request $request, OrishaRepository $orishaRepository): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $categoria = $request->query->get('categoria');
        $search = $request->query->get('search');

        if ($search) {
            $orishas = $orishaRepository->search($search);
            $result = array_map(fn($orisha) => $orisha->toArray(), $orishas);
        } elseif ($categoria) {
            $orishas = $orishaRepository->findByCategoria($categoria);
            $result = array_map(fn($orisha) => $orisha->toArray(), $orishas);
        } else {
            // Return grouped by category
            $grouped = $orishaRepository->findGroupedByCategory();
            $result = [];
            foreach ($grouped as $cat => $orishas) {
                $result[$cat] = array_map(fn($orisha) => $orisha->toArray(), $orishas);
            }
        }

        return new JsonResponse($result);
    }

    #[Route('/{id}', name: 'orisha_detail', methods: ['GET'])]
    public function getOrisha(Request $request, int $id, OrishaRepository $orishaRepository): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $orisha = $orishaRepository->find($id);
        if (!$orisha) {
            return new JsonResponse(['error' => 'Orisha no encontrado'], 404);
        }

        return new JsonResponse($orisha->toArray());
    }

    #[Route('/nombre/{nombre}', name: 'orisha_by_name', methods: ['GET'])]
    public function getOrishaByName(Request $request, string $nombre, OrishaRepository $orishaRepository): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $orisha = $orishaRepository->findByNombre($nombre);
        if (!$orisha) {
            return new JsonResponse(['error' => 'Orisha no encontrado'], 404);
        }

        return new JsonResponse($orisha->toArray());
    }

    #[Route('', name: 'orisha_create', methods: ['POST'])]
    public function createOrisha(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        // Only allow certain roles to create orishas
        if (!in_array($user->getRole(), ['babalawo', 'admin'])) {
            return new JsonResponse(['error' => 'No autorizado para crear orishas'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['nombre']) || !isset($data['dominio'])) {
            return new JsonResponse(['error' => 'Nombre y dominio son requeridos'], 400);
        }

        $orisha = new Orisha();
        $orisha->setNombre($data['nombre']);
        $orisha->setDominio($data['dominio']);
        
        if (isset($data['otros_nombres'])) {
            $orisha->setOtrosNombres($data['otros_nombres']);
        }
        if (isset($data['color'])) {
            $orisha->setColor($data['color']);
        }
        if (isset($data['numero'])) {
            $orisha->setNumero($data['numero']);
        }
        if (isset($data['atributos'])) {
            $orisha->setAtributos($data['atributos']);
        }
        if (isset($data['sincretismo'])) {
            $orisha->setSincretismo($data['sincretismo']);
        }
        if (isset($data['dia'])) {
            $orisha->setDia($data['dia']);
        }
        if (isset($data['categoria'])) {
            $orisha->setCategoria($data['categoria']);
        }

        try {
            $entityManager->persist($orisha);
            $entityManager->flush();

            return new JsonResponse($orisha->toArray(), 201);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error al crear orisha: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'orisha_update', methods: ['PUT'])]
    public function updateOrisha(Request $request, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        // Only allow certain roles to update orishas
        if (!in_array($user->getRole(), ['babalawo', 'admin'])) {
            return new JsonResponse(['error' => 'No autorizado para editar orishas'], 403);
        }

        $orisha = $entityManager->getRepository(Orisha::class)->find($id);
        if (!$orisha) {
            return new JsonResponse(['error' => 'Orisha no encontrado'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['nombre'])) {
            $orisha->setNombre($data['nombre']);
        }
        if (isset($data['dominio'])) {
            $orisha->setDominio($data['dominio']);
        }
        if (isset($data['otros_nombres'])) {
            $orisha->setOtrosNombres($data['otros_nombres']);
        }
        if (isset($data['color'])) {
            $orisha->setColor($data['color']);
        }
        if (isset($data['numero'])) {
            $orisha->setNumero($data['numero']);
        }
        if (isset($data['atributos'])) {
            $orisha->setAtributos($data['atributos']);
        }
        if (isset($data['sincretismo'])) {
            $orisha->setSincretismo($data['sincretismo']);
        }
        if (isset($data['dia'])) {
            $orisha->setDia($data['dia']);
        }
        if (isset($data['categoria'])) {
            $orisha->setCategoria($data['categoria']);
        }

        $orisha->setUpdatedAt(new \DateTime());

        try {
            $entityManager->flush();
            return new JsonResponse($orisha->toArray());
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error al actualizar orisha: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/popular', name: 'orishas_popular', methods: ['GET'])]
    public function getMostPopularOrishas(Request $request, OrishaRepository $orishaRepository): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $limit = min(20, max(5, (int)$request->query->get('limit', 10)));
        $orishas = $orishaRepository->findMostPopular($limit);

        $result = array_map(fn($orisha) => $orisha->toArray(), $orishas);

        return new JsonResponse($result);
    }
} 