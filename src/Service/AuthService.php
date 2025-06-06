<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\Request;

class AuthService
{
    private string $jwtSecret;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
        $this->entityManager = $entityManager;
    }

    public function getUserFromToken(Request $request): ?User
    {
        $authHeader = $request->headers->get('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }

        $token = substr($authHeader, 7);

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Use UUID instead of ID for better security and consistency
            $userUuid = $decoded->user_uuid ?? $decoded->user_id; // Fallback for existing tokens
            
            if (!$userUuid) {
                return null;
            }

            return $this->entityManager->getRepository(User::class)->findOneBy(['uuid' => $userUuid]);
            
        } catch (\Exception $e) {
            return null;
        }
    }

    public function generateToken(User $user): string
    {
        $payload = [
            'user_uuid' => $user->getUuid(),
            'user_id' => $user->getId(), // Keep for backwards compatibility
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'role' => $user->getRole(),
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function requireAuth(Request $request): User
    {
        $user = $this->getUserFromToken($request);
        
        if (!$user) {
            throw new \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException('Bearer', 'Token inválido o expirado');
        }
        
        return $user;
    }

    public function getUserByUuid(string $uuid): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['uuid' => $uuid]);
    }

    public function getUserByEmail(string $email): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
    }
} 