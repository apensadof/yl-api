<?php

namespace App\Controller;

use App\Entity\Ahijado;
use App\Entity\User;
use App\Repository\AhijadoRepository;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AhijadoController extends AbstractController
{
    private $entityManager;
    private $ahijadoRepository;
    private $userRepository;
    private $validator;
    private $authService;

    public function __construct(
        EntityManagerInterface $entityManager,
        AhijadoRepository $ahijadoRepository,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        AuthService $authService
    ) {
        $this->entityManager = $entityManager;
        $this->ahijadoRepository = $ahijadoRepository;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
        $this->authService = $authService;
    }

    #[Route('/ahijados', name: 'ahijados_list', methods: ['GET'])]
    public function getAhijados(Request $request): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return $this->json(['error' => 'No autorizado'], 401);
        }

        $ahijados = $this->ahijadoRepository->findByUser($user);
        
        return $this->json(array_map(fn($ahijado) => $ahijado->toArray(), $ahijados));
    }

    #[Route('/ahijados', name: 'ahijados_create', methods: ['POST'])]
    public function createAhijado(Request $request): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return $this->json(['error' => 'No autorizado'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!$this->validateAhijadoData($data)) {
            return $this->json([
                'error' => 'Datos inválidos',
                'details' => $this->getValidationErrors($data)
            ], 400);
        }

        $ahijado = new Ahijado();
        $ahijado->setUser($user);
        $ahijado->setName($data['name']);
        $ahijado->setStatus($data['status']);
        
        if (isset($data['phone'])) {
            $ahijado->setPhone($data['phone']);
        }
        
        if (isset($data['email'])) {
            $ahijado->setEmail($data['email']);
        }
        
        if (isset($data['address'])) {
            $ahijado->setAddress($data['address']);
        }
        
        if (isset($data['notes'])) {
            $ahijado->setNotes($data['notes']);
        }
        
        if (isset($data['birthdate'])) {
            $ahijado->setBirthdate(new \DateTime($data['birthdate']));
        }
        
        if (isset($data['initiationDate'])) {
            $ahijado->setInitiationDate(new \DateTime($data['initiationDate']));
        }

        $this->entityManager->persist($ahijado);
        $this->entityManager->flush();

        return $this->json($ahijado->toArray(), 201);
    }

    #[Route('/ahijados/search', name: 'ahijados_search', methods: ['GET'])]
    public function searchAhijados(Request $request): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return $this->json(['error' => 'No autorizado'], 401);
        }

        $term = $request->query->get('term');
        
        if (!$term) {
            return $this->json(['error' => 'Término de búsqueda requerido'], 400);
        }

        $ahijados = $this->ahijadoRepository->searchByTerm($user, $term);
        
        return $this->json(array_map(fn($ahijado) => $ahijado->toArray(), $ahijados));
    }

    #[Route('/godchildren', name: 'godchildren_list', methods: ['GET'])]
    public function getGodchildren(Request $request): JsonResponse
    {
        return $this->getAhijados($request);
    }

    #[Route('/godchildren', name: 'godchildren_create', methods: ['POST'])]
    public function createGodchild(Request $request): JsonResponse
    {
        return $this->createAhijado($request);
    }

    #[Route('/godchildren/search', name: 'godchildren_search', methods: ['GET'])]
    public function searchGodchildren(Request $request): JsonResponse
    {
        return $this->searchAhijados($request);
    }

    private function validateAhijadoData(array $data): bool
    {
        return isset($data['name']) && 
               isset($data['status']) && 
               strlen($data['name']) >= 2 &&
               in_array($data['status'], ['Aleyo', 'Iniciado', 'Babalawo', 'Iyanifa']);
    }

    private function getValidationErrors(array $data): array
    {
        $errors = [];
        
        if (!isset($data['name']) || strlen($data['name']) < 2) {
            $errors[] = 'El nombre es requerido y debe tener al menos 2 caracteres';
        }
        
        if (!isset($data['status']) || !in_array($data['status'], ['Aleyo', 'Iniciado', 'Babalawo', 'Iyanifa'])) {
            $errors[] = 'El status debe ser válido (Aleyo, Iniciado, Babalawo, Iyanifa)';
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email debe tener un formato válido';
        }
        
        return $errors;
    }
} 