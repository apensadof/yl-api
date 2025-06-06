<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/auth', name: 'auth_')]
class AuthController extends AbstractController
{
    private $entityManager;
    private $userRepository;
    private $passwordHasher;
    private string $jwtSecret;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json([
                'error' => 'Email y password son requeridos'
            ], 400);
        }

        $user = $this->userRepository->findByEmail($data['email']);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json([
                'error' => 'Credenciales inválidas'
            ], 401);
        }

        $payload = [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ];

        $token = JWT::encode($payload, $this->jwtSecret, 'HS256');

        return $this->json([
            'token' => $token,
            'user' => $user->toArray()
        ]);
    }

    #[Route('/verify', name: 'verify', methods: ['GET'])]
    public function verify(Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->json([
                'error' => 'Token no proporcionado'
            ], 401);
        }

        $token = substr($authHeader, 7);

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            $user = $this->userRepository->find($decoded->user_id);

            if (!$user) {
                return $this->json([
                    'error' => 'Usuario no encontrado'
                ], 401);
            }

            return $this->json($user->toArray());

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Token inválido o expirado'
            ], 401);
        }
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
} 