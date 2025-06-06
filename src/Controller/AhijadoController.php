<?php

namespace App\Controller;

use App\Entity\Ahijado;
use App\Entity\User;
use App\Entity\Orisha;
use App\Entity\Ceremonia;
use App\Repository\AhijadoRepository;
use App\Repository\UserRepository;
use App\Repository\OrishaRepository;
use App\Repository\CeremoniaRepository;
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
    private $orishaRepository;
    private $ceremoniaRepository;
    private $validator;
    private $authService;

    public function __construct(
        EntityManagerInterface $entityManager,
        AhijadoRepository $ahijadoRepository,
        UserRepository $userRepository,
        OrishaRepository $orishaRepository,
        CeremoniaRepository $ceremoniaRepository,
        ValidatorInterface $validator,
        AuthService $authService
    ) {
        $this->entityManager = $entityManager;
        $this->ahijadoRepository = $ahijadoRepository;
        $this->userRepository = $userRepository;
        $this->orishaRepository = $orishaRepository;
        $this->ceremoniaRepository = $ceremoniaRepository;
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

        // Handle orisha cabeza
        if (isset($data['orishaCabeza'])) {
            $orishaCabeza = null;
            if (is_numeric($data['orishaCabeza'])) {
                $orishaCabeza = $this->orishaRepository->find($data['orishaCabeza']);
            } else {
                $orishaCabeza = $this->orishaRepository->findByNombre($data['orishaCabeza']);
            }
            if ($orishaCabeza) {
                $ahijado->setOrishaCabeza($orishaCabeza);
            }
        }

        // Handle orishas recibidos
        if (isset($data['orishasRecibidos']) && is_array($data['orishasRecibidos'])) {
            foreach ($data['orishasRecibidos'] as $orishaData) {
                $orisha = null;
                if (is_numeric($orishaData)) {
                    $orisha = $this->orishaRepository->find($orishaData);
                } else {
                    $orisha = $this->orishaRepository->findByNombre($orishaData);
                }
                if ($orisha) {
                    $ahijado->addOrishaRecibido($orisha);
                }
            }
        }

        // Handle ceremonias realizadas
        if (isset($data['ceremoniasRealizadas']) && is_array($data['ceremoniasRealizadas'])) {
            foreach ($data['ceremoniasRealizadas'] as $ceremoniaData) {
                $ceremonia = null;
                if (is_numeric($ceremoniaData)) {
                    $ceremonia = $this->ceremoniaRepository->find($ceremoniaData);
                } else {
                    $ceremonia = $this->ceremoniaRepository->findByNombre($ceremoniaData);
                }
                if ($ceremonia) {
                    $ahijado->addCeremoniaRealizada($ceremonia);
                }
            }
        }

        $this->entityManager->persist($ahijado);
        $this->entityManager->flush();

        return $this->json($ahijado->toArray(), 201);
    }

    #[Route('/ahijados/{id}', name: 'ahijado_get', methods: ['GET'])]
    public function getAhijado(Request $request, int $id): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return $this->json(['error' => 'No autorizado'], 401);
        }

        $ahijado = $this->ahijadoRepository->findOneBy(['id' => $id, 'user' => $user]);
        
        if (!$ahijado) {
            return $this->json(['error' => 'Ahijado no encontrado'], 404);
        }
        
        return $this->json($ahijado->toArray());
    }

    #[Route('/ahijados/{id}', name: 'ahijado_update', methods: ['PUT'])]
    public function updateAhijado(Request $request, int $id): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return $this->json(['error' => 'No autorizado'], 401);
        }

        $ahijado = $this->ahijadoRepository->findOneBy(['id' => $id, 'user' => $user]);
        
        if (!$ahijado) {
            return $this->json(['error' => 'Ahijado no encontrado'], 404);
        }

        $data = json_decode($request->getContent(), true);

        // Update basic fields
        if (isset($data['name'])) {
            $ahijado->setName($data['name']);
        }
        if (isset($data['status'])) {
            $ahijado->setStatus($data['status']);
        }
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

        // Update orisha cabeza
        if (isset($data['orishaCabeza'])) {
            $orishaCabeza = null;
            if (is_numeric($data['orishaCabeza'])) {
                $orishaCabeza = $this->orishaRepository->find($data['orishaCabeza']);
            } else {
                $orishaCabeza = $this->orishaRepository->findByNombre($data['orishaCabeza']);
            }
            $ahijado->setOrishaCabeza($orishaCabeza);
        }

        // Update orishas recibidos
        if (isset($data['orishasRecibidos']) && is_array($data['orishasRecibidos'])) {
            // Clear existing orishas
            foreach ($ahijado->getOrishasRecibidos() as $orisha) {
                $ahijado->removeOrishaRecibido($orisha);
            }
            
            // Add new orishas
            foreach ($data['orishasRecibidos'] as $orishaData) {
                $orisha = null;
                if (is_numeric($orishaData)) {
                    $orisha = $this->orishaRepository->find($orishaData);
                } else {
                    $orisha = $this->orishaRepository->findByNombre($orishaData);
                }
                if ($orisha) {
                    $ahijado->addOrishaRecibido($orisha);
                }
            }
        }

        // Update ceremonias realizadas
        if (isset($data['ceremoniasRealizadas']) && is_array($data['ceremoniasRealizadas'])) {
            // Clear existing ceremonias
            foreach ($ahijado->getCeremoniasRealizadas() as $ceremonia) {
                $ahijado->removeCeremoniaRealizada($ceremonia);
            }
            
            // Add new ceremonias
            foreach ($data['ceremoniasRealizadas'] as $ceremoniaData) {
                $ceremonia = null;
                if (is_numeric($ceremoniaData)) {
                    $ceremonia = $this->ceremoniaRepository->find($ceremoniaData);
                } else {
                    $ceremonia = $this->ceremoniaRepository->findByNombre($ceremoniaData);
                }
                if ($ceremonia) {
                    $ahijado->addCeremoniaRealizada($ceremonia);
                }
            }
        }

        $ahijado->setUpdatedAt(new \DateTime());
        $this->entityManager->flush();

        return $this->json($ahijado->toArray());
    }

    #[Route('/ahijados/{id}', name: 'ahijado_delete', methods: ['DELETE'])]
    public function deleteAhijado(Request $request, int $id): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return $this->json(['error' => 'No autorizado'], 401);
        }

        $ahijado = $this->ahijadoRepository->findOneBy(['id' => $id, 'user' => $user]);
        
        if (!$ahijado) {
            return $this->json(['error' => 'Ahijado no encontrado'], 404);
        }

        $this->entityManager->remove($ahijado);
        $this->entityManager->flush();

        return $this->json(['message' => 'Ahijado eliminado exitosamente']);
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