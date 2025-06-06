<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Oddun;
use App\Entity\ComplementoOddun;
use App\Entity\User;
use App\Repository\UserRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class OddunController extends AbstractController
{
    private $entityManager;
    private $userRepository;
    private string $jwtSecret;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
    }

    #[Route('/oddun/{bin}', name: 'oddun_get', methods: ['GET'])]
    public function getOddun(Request $request): JsonResponse
    {
        $bin = $request->get('bin');
        
        if (!$bin) {
            return $this->json([
                'status' => 400,
                'message' => 'Incomplete request'
            ], 400);
        }

        $oddunRepository = $this->entityManager->getRepository(Oddun::class);
        $oddun = $oddunRepository->findOneBy(['bin' => $bin]);

        if (!$oddun) {
            return $this->json([
                'status' => 404,
                'message' => 'No results found'
            ], 404);
        }

        $complementoRepository = $this->entityManager->getRepository(ComplementoOddun::class);
        $complemento = $complementoRepository->findOneBy(['id' => $oddun->getId()]);

        if (!$complemento) {
            return $this->json([
                'status' => 500,
                'message' => 'Ocurrio un error'
            ], 500);
        }

        $result = array_merge(
            $oddun->toArray(),
            $complemento->toArray()
        );

        return $this->json([
            'status' => 200,
            'message' => 'La información se obtuvo correctamente',
            'data' => base64_encode(json_encode($result))
        ]);
    }

    #[Route('/odduns/{oddunId}', name: 'oddun_by_id', methods: ['GET'])]
    public function getOddunById(Request $request, int $oddunId): JsonResponse
    {
        $user = $this->getUserFromToken($request);
        
        if (!$user) {
            return $this->json(['error' => 'No autorizado'], 401);
        }

        $oddunRepository = $this->entityManager->getRepository(Oddun::class);
        $oddun = $oddunRepository->find($oddunId);

        if (!$oddun) {
            return $this->json(['error' => 'Oddun no encontrado'], 404);
        }

        $complementoRepository = $this->entityManager->getRepository(ComplementoOddun::class);
        $complemento = $complementoRepository->findOneBy(['id' => $oddun->getId()]);

        $result = array_merge(
            $oddun->toArray(),
            $complemento ? $complemento->toArray() : []
        );

        // Transform to API format
        $response = [
            'id' => $result['id'],
            'uid' => $result['uid'],
            'name' => $result['name'],
            'bin' => $result['bin'],
            'meaning' => $result['diceifa'] ?? 'Signo de Ifá',
            'consejos_ire' => $this->parseAdvice($result['ire'] ?? ''),
            'consejos_osogbo' => $this->parseAdvice($result['osogbo'] ?? ''),
            'patakies' => $this->parsePatakies($result['patakies'] ?? ''),
            'prohibiciones' => $this->parseProhibitions($result['nace'] ?? ''),
            'description' => $result['historia'] ?? 'Descripción del oddun'
        ];

        return $this->json($response);
    }

    #[Route('/odduns/details/{oddunName}', name: 'oddun_by_name', methods: ['GET'])]
    public function getOddunByName(Request $request, string $oddunName): JsonResponse
    {
        $user = $this->getUserFromToken($request);
        
        if (!$user) {
            return $this->json(['error' => 'No autorizado'], 401);
        }

        $oddunRepository = $this->entityManager->getRepository(Oddun::class);
        $oddun = $oddunRepository->findOneBy(['name' => urldecode($oddunName)]);

        if (!$oddun) {
            return $this->json(['error' => 'Oddun no encontrado'], 404);
        }

        $complementoRepository = $this->entityManager->getRepository(ComplementoOddun::class);
        $complemento = $complementoRepository->findOneBy(['id' => $oddun->getId()]);

        $result = array_merge(
            $oddun->toArray(),
            $complemento ? $complemento->toArray() : []
        );

        // Transform to API format
        $response = [
            'id' => $result['id'],
            'uid' => $result['uid'],
            'name' => $result['name'],
            'bin' => $result['bin'],
            'meaning' => $result['diceifa'] ?? 'Signo de Ifá',
            'consejos_ire' => $this->parseAdvice($result['ire'] ?? ''),
            'consejos_osogbo' => $this->parseAdvice($result['osogbo'] ?? ''),
            'patakies' => $this->parsePatakies($result['patakies'] ?? ''),
            'prohibiciones' => $this->parseProhibitions($result['nace'] ?? ''),
            'description' => $result['historia'] ?? 'Descripción del oddun'
        ];

        return $this->json($response);
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

    private function parseAdvice(string $text): array
    {
        if (empty($text)) {
            return [];
        }
        
        return array_filter(explode("\n", $text), fn($line) => !empty(trim($line)));
    }

    private function parsePatakies(string $text): array
    {
        if (empty($text)) {
            return [];
        }
        
        return array_filter(explode("\n\n", $text), fn($story) => !empty(trim($story)));
    }

    private function parseProhibitions(string $text): array
    {
        if (empty($text)) {
            return [];
        }
        
        return array_filter(explode("\n", $text), fn($line) => !empty(trim($line)));
    }
} 