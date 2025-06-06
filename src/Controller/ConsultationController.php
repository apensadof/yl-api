<?php

namespace App\Controller;

use App\Entity\Consultation;
use App\Entity\User;
use App\Repository\ConsultationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConsultationController extends AbstractController
{
    private $entityManager;
    private $consultationRepository;
    private $userRepository;
    private string $jwtSecret;

    public function __construct(
        EntityManagerInterface $entityManager,
        ConsultationRepository $consultationRepository,
        UserRepository $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->consultationRepository = $consultationRepository;
        $this->userRepository = $userRepository;
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
    }

    #[Route('/consultations', name: 'consultations_list', methods: ['GET'])]
    public function getConsultations(Request $request): JsonResponse
    {
        $user = $this->getUserFromToken($request);
        
        if (!$user) {
            return $this->json(['error' => 'No autorizado'], 401);
        }

        $consultations = $this->consultationRepository->findByUser($user);
        
        return $this->json(array_map(fn($consultation) => $consultation->toArray(), $consultations));
    }

    #[Route('/consultations', name: 'consultations_create', methods: ['POST'])]
    public function createConsultation(Request $request): JsonResponse
    {
        $user = $this->getUserFromToken($request);
        
        if (!$user) {
            return $this->json(['error' => 'No autorizado'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!$this->validateConsultationData($data)) {
            return $this->json([
                'error' => 'Datos inválidos',
                'details' => $this->getValidationErrors($data)
            ], 400);
        }

        $consultation = new Consultation();
        $consultation->setUser($user);
        $consultation->setClientName($data['clientName']);
        $consultation->setType($data['type']);
        $consultation->setSigns($data['signs']);
        $consultation->setIreOsogbo($data['ire_osogbo']);
        $consultation->setDate(new \DateTime($data['date']));
        
        if (isset($data['notes'])) {
            $consultation->setNotes($data['notes']);
        }

        $this->entityManager->persist($consultation);
        $this->entityManager->flush();

        return $this->json($consultation->toArray(), 201);
    }

    private function getUserFromToken(Request $request): ?User
    {
        $authHeader = $request->headers->get('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = substr($authHeader, 7);

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            return $this->userRepository->find($decoded->user_id);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function validateConsultationData(array $data): bool
    {
        return isset($data['clientName']) && 
               isset($data['type']) && 
               isset($data['signs']) && 
               isset($data['ire_osogbo']) && 
               isset($data['date']) &&
               strlen($data['clientName']) >= 2 &&
               is_array($data['signs']) &&
               count($data['signs']) > 0 &&
               in_array($data['ire_osogbo'], ['ire', 'osogbo']);
    }

    private function getValidationErrors(array $data): array
    {
        $errors = [];
        
        if (!isset($data['clientName']) || strlen($data['clientName']) < 2) {
            $errors[] = 'El nombre del cliente es requerido y debe tener al menos 2 caracteres';
        }
        
        if (!isset($data['type'])) {
            $errors[] = 'El tipo de consulta es requerido';
        }
        
        if (!isset($data['signs']) || !is_array($data['signs']) || count($data['signs']) == 0) {
            $errors[] = 'Los signos son requeridos y deben ser un array con al menos un elemento';
        }
        
        if (!isset($data['ire_osogbo']) || !in_array($data['ire_osogbo'], ['ire', 'osogbo'])) {
            $errors[] = 'El campo ire_osogbo debe ser "ire" o "osogbo"';
        }
        
        if (!isset($data['date'])) {
            $errors[] = 'La fecha es requerida';
        } else {
            try {
                new \DateTime($data['date']);
            } catch (\Exception $e) {
                $errors[] = 'La fecha debe tener un formato válido';
            }
        }
        
        return $errors;
    }
} 