<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\PasswordResetToken;
use App\Repository\UserRepository;
use App\Repository\PasswordResetTokenRepository;
use App\Service\EmailService;
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
    private $passwordResetTokenRepository;
    private $emailService;
    private string $jwtSecret;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        PasswordResetTokenRepository $passwordResetTokenRepository,
        EmailService $emailService
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->passwordResetTokenRepository = $passwordResetTokenRepository;
        $this->emailService = $emailService;
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
            'user_uuid' => $user->getUuid(),
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
            $user = $this->userRepository->findOneBy(['uuid' => $decoded->user_uuid]);

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

    #[Route('/forgot-password', name: 'forgot_password', methods: ['POST'])]
    public function forgotPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'])) {
            return $this->json([
                'error' => 'Email es requerido'
            ], 400);
        }

        $email = $data['email'];
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'error' => 'Formato de email inválido'
            ], 400);
        }

        $user = $this->userRepository->findByEmail($email);

        // Always return success for security (don't reveal if email exists)
        if (!$user) {
            return $this->json([
                'message' => 'Si el email existe, recibirás un enlace de restablecimiento'
            ]);
        }

        // Invalidate existing tokens for this user
        $this->passwordResetTokenRepository->invalidateUserTokens($user);

        // Generate new reset token
        $resetToken = bin2hex(random_bytes(32));
        
        $passwordResetToken = new PasswordResetToken();
        $passwordResetToken->setUser($user);
        $passwordResetToken->setToken($resetToken);
        $passwordResetToken->setExpiresAt(new \DateTime('+1 hour'));

        try {
            $this->entityManager->persist($passwordResetToken);
            $this->entityManager->flush();

            // Send email
            $emailSent = $this->emailService->sendPasswordResetEmail(
                $user->getEmail(),
                $user->getName(),
                $resetToken
            );

            if (!$emailSent) {
                error_log('Failed to send password reset email to: ' . $user->getEmail());
            }

            return $this->json([
                'message' => 'Si el email existe, recibirás un enlace de restablecimiento'
            ]);

        } catch (\Exception $e) {
            error_log('Error in forgot password: ' . $e->getMessage());
            return $this->json([
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    #[Route('/reset-password', name: 'reset_password', methods: ['POST'])]
    public function resetPassword(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['token']) || !isset($data['password'])) {
            return $this->json([
                'error' => 'Token y nueva contraseña son requeridos'
            ], 400);
        }

        $tokenString = $data['token'];
        $newPassword = $data['password'];

        if (strlen($newPassword) < 6) {
            return $this->json([
                'error' => 'La contraseña debe tener al menos 6 caracteres'
            ], 400);
        }

        $resetToken = $this->passwordResetTokenRepository->findValidTokenByTokenString($tokenString);

        if (!$resetToken || !$resetToken->isValid()) {
            return $this->json([
                'error' => 'Token inválido o expirado'
            ], 400);
        }

        $user = $resetToken->getUser();

        try {
            // Update password
            $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
            $user->setPasswordHash($hashedPassword);
            $user->setUpdatedAt(new \DateTime());

            // Mark token as used
            $resetToken->setUsed(true);

            // Invalidate all other tokens for this user
            $this->passwordResetTokenRepository->invalidateUserTokens($user);

            $this->entityManager->flush();

            return $this->json([
                'message' => 'Contraseña actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            error_log('Error in reset password: ' . $e->getMessage());
            return $this->json([
                'error' => 'Error interno del servidor'
            ], 500);
        }
    }

    #[Route('/validate-reset-token', name: 'validate_reset_token', methods: ['POST'])]
    public function validateResetToken(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['token'])) {
            return $this->json([
                'error' => 'Token es requerido'
            ], 400);
        }

        $tokenString = $data['token'];
        $resetToken = $this->passwordResetTokenRepository->findValidTokenByTokenString($tokenString);

        if (!$resetToken || !$resetToken->isValid()) {
            return $this->json([
                'valid' => false,
                'error' => 'Token inválido o expirado'
            ], 400);
        }

        return $this->json([
            'valid' => true,
            'user_email' => $resetToken->getUser()->getEmail(),
            'expires_at' => $resetToken->getExpiresAt()->format('Y-m-d H:i:s')
        ]);
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
            return $this->userRepository->findOneBy(['uuid' => $decoded->user_uuid]);
        } catch (\Exception $e) {
            return null;
        }
    }
} 