<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\PasswordResetToken;
use App\Repository\UserRepository;
use App\Repository\PasswordResetTokenRepository;
use App\Service\EmailService;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
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
    private $authService;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        PasswordResetTokenRepository $passwordResetTokenRepository,
        EmailService $emailService,
        AuthService $authService
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->passwordResetTokenRepository = $passwordResetTokenRepository;
        $this->emailService = $emailService;
        $this->authService = $authService;
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate required fields
        $validationErrors = $this->validateRegistrationData($data);
        if (!empty($validationErrors)) {
            return $this->json([
                'error' => 'Datos de validación inválidos',
                'details' => $validationErrors
            ], 422);
        }

        // Check if email already exists
        $existingUser = $this->userRepository->findByEmail($data['email']);
        if ($existingUser) {
            return $this->json([
                'error' => 'El email ya está registrado'
            ], 400);
        }

        try {
            // Create new user
            $user = new User();
            $user->setName($data['name']);
            $user->setEmail($data['email']);
            $user->setRole($data['role'] ?? 'santero');
            $user->setStatus('pending'); // Users need admin approval
            
            // Hash password
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPasswordHash($hashedPassword);

            // Set optional fields
            if (isset($data['spiritualLevel'])) {
                $user->setSpiritualLevel($data['spiritualLevel']);
            }
            if (isset($data['phone'])) {
                $user->setPhone($data['phone']);
            }
            if (isset($data['city'])) {
                $user->setCity($data['city']);
            }
            if (isset($data['notes'])) {
                $user->setNotes($data['notes']);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Send welcome email
            try {
                $this->emailService->sendWelcomeEmail(
                    $user->getEmail(),
                    $user->getName()
                );
            } catch (\Exception $e) {
                error_log('Failed to send welcome email: ' . $e->getMessage());
            }

            return $this->json([
                'message' => 'Usuario registrado exitosamente. Espera la aprobación del administrador.',
                'userId' => $user->getId()
            ], 201);

        } catch (\Exception $e) {
            error_log('Error in registration: ' . $e->getMessage());
            return $this->json([
                'error' => 'Error interno del servidor'
            ], 500);
        }
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

        // Check if user is approved
        if ($user->getStatus() !== 'approved') {
            $statusMessages = [
                'pending' => 'Tu cuenta está pendiente de aprobación por el administrador',
                'suspended' => 'Tu cuenta ha sido suspendida. Contacta al administrador',
                'rejected' => 'Tu solicitud de registro ha sido rechazada'
            ];
            
            return $this->json([
                'error' => $statusMessages[$user->getStatus()] ?? 'Estado de cuenta inválido'
            ], 403);
        }

        $token = $this->authService->generateToken($user);

        return $this->json([
            'token' => $token,
            'user' => $user->toArray()
        ]);
    }

    #[Route('/verify', name: 'verify', methods: ['GET'])]
    public function verify(Request $request): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
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

    private function validateRegistrationData(?array $data): array
    {
        $errors = [];

        if (empty($data)) {
            return ['general' => 'Datos requeridos'];
        }

        // Required fields
        if (empty($data['name'])) {
            $errors['name'] = 'El nombre es requerido';
        } elseif (strlen($data['name']) < 2) {
            $errors['name'] = 'El nombre debe tener al menos 2 caracteres';
        } elseif (strlen($data['name']) > 255) {
            $errors['name'] = 'El nombre no puede exceder 255 caracteres';
        }

        if (empty($data['email'])) {
            $errors['email'] = 'El email es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'El email debe ser válido';
        } elseif (strlen($data['email']) > 255) {
            $errors['email'] = 'El email no puede exceder 255 caracteres';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'La contraseña es requerida';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'La contraseña debe tener al menos 8 caracteres';
        } elseif (strlen($data['password']) > 255) {
            $errors['password'] = 'La contraseña no puede exceder 255 caracteres';
        }

        // Optional field validations
        if (isset($data['role'])) {
            $allowedRoles = ['santero', 'babalawo', 'iyanifa', 'aleyo', 'admin'];
            if (!in_array($data['role'], $allowedRoles)) {
                $errors['role'] = 'El rol debe ser uno de: ' . implode(', ', $allowedRoles);
            }
        }

        if (isset($data['spiritualLevel'])) {
            $allowedLevels = ['aleyo', 'santo_hecho', 'babalawo', 'iyanifa', 'oriate', 'oyugbona'];
            if (!in_array($data['spiritualLevel'], $allowedLevels)) {
                $errors['spiritualLevel'] = 'El nivel espiritual debe ser uno de: ' . implode(', ', $allowedLevels);
            }
        }

        if (isset($data['phone']) && !empty($data['phone'])) {
            if (strlen($data['phone']) > 20) {
                $errors['phone'] = 'El teléfono no puede exceder 20 caracteres';
            }
            // Basic phone validation (allow +, -, spaces, parentheses, and numbers)
            if (!preg_match('/^[\+\-\s\(\)\d]+$/', $data['phone'])) {
                $errors['phone'] = 'El formato del teléfono no es válido';
            }
        }

        if (isset($data['city']) && !empty($data['city'])) {
            if (strlen($data['city']) > 100) {
                $errors['city'] = 'La ciudad no puede exceder 100 caracteres';
            }
        }

        if (isset($data['notes']) && !empty($data['notes'])) {
            if (strlen($data['notes']) > 1000) {
                $errors['notes'] = 'Las notas no pueden exceder 1000 caracteres';
            }
        }

        return $errors;
    }
} 