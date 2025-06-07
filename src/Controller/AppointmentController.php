<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/appointments')]
class AppointmentController extends AbstractController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    #[Route('/today', name: 'appointments_today', methods: ['GET'])]
    public function getTodayAppointments(Request $request, AppointmentRepository $appointmentRepository): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $appointments = $appointmentRepository->findTodayByUser($user);
        
        $result = [];
        foreach ($appointments as $appointment) {
            $result[] = $appointment->toArray();
        }

        return new JsonResponse($result);
    }

    #[Route('', name: 'appointments_create', methods: ['POST'])]
    public function createAppointment(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $data = json_decode($request->getContent(), true);
        
        // Validation
        $errors = [];
        if (!isset($data['clientName']) || empty($data['clientName'])) {
            $errors[] = 'El nombre del cliente es requerido';
        }
        if (!isset($data['type']) || empty($data['type'])) {
            $errors[] = 'El tipo de cita es requerido';
        }
        if (!isset($data['date']) || empty($data['date'])) {
            $errors[] = 'La fecha es requerida';
        }
        if (!isset($data['time']) || empty($data['time'])) {
            $errors[] = 'La hora es requerida';
        }
        if (!isset($data['duration']) || !is_numeric($data['duration']) || $data['duration'] <= 0) {
            $errors[] = 'La duración debe ser un número válido mayor a 0';
        }

        if (!empty($errors)) {
            return new JsonResponse(['error' => 'Datos inválidos', 'details' => $errors], 400);
        }

        try {
            $appointment = new Appointment();
            $appointment->setUser($user);
            $appointment->setClientName($data['clientName']);
            $appointment->setType($data['type']);
            $appointment->setDate(new \DateTime($data['date']));
            $appointment->setTime(new \DateTime($data['time']));
            $appointment->setDuration((int)$data['duration']);
            
            if (isset($data['title'])) {
                $appointment->setTitle($data['title']);
            }
            if (isset($data['status'])) {
                $appointment->setStatus($data['status']);
            }
            if (isset($data['notes'])) {
                $appointment->setNotes($data['notes']);
            }

            // Check for conflicts
            $appointmentRepository = $entityManager->getRepository(Appointment::class);
            $conflicts = $appointmentRepository->findConflictingAppointments(
                $user,
                $appointment->getDate(),
                $appointment->getTime(),
                $appointment->getDuration()
            );

            if (!empty($conflicts)) {
                return new JsonResponse([
                    'error' => 'Conflicto de horario',
                    'message' => 'Ya tienes una cita en ese horario'
                ], 409);
            }

            $entityManager->persist($appointment);
            $entityManager->flush();

            return new JsonResponse($appointment->toArray(), 201);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error al crear la cita: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'appointments_update', methods: ['PUT'])]
    public function updateAppointment(Request $request, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $appointment = $entityManager->getRepository(Appointment::class)->find($id);
        if (!$appointment) {
            return new JsonResponse(['error' => 'Cita no encontrada'], 404);
        }

        if ($appointment->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'No autorizado'], 403);
        }

        $data = json_decode($request->getContent(), true);

        try {
            if (isset($data['clientName'])) {
                $appointment->setClientName($data['clientName']);
            }
            if (isset($data['type'])) {
                $appointment->setType($data['type']);
            }
            if (isset($data['title'])) {
                $appointment->setTitle($data['title']);
            }
            if (isset($data['date'])) {
                $appointment->setDate(new \DateTime($data['date']));
            }
            if (isset($data['time'])) {
                $appointment->setTime(new \DateTime($data['time']));
            }
            if (isset($data['duration'])) {
                $appointment->setDuration((int)$data['duration']);
            }
            if (isset($data['status'])) {
                $appointment->setStatus($data['status']);
            }
            if (isset($data['notes'])) {
                $appointment->setNotes($data['notes']);
            }

            // Check for conflicts if date/time changed
            if (isset($data['date']) || isset($data['time']) || isset($data['duration'])) {
                $appointmentRepository = $entityManager->getRepository(Appointment::class);
                $conflicts = $appointmentRepository->findConflictingAppointments(
                    $user,
                    $appointment->getDate(),
                    $appointment->getTime(),
                    $appointment->getDuration(),
                    $appointment->getId()
                );

                if (!empty($conflicts)) {
                    return new JsonResponse([
                        'error' => 'Conflicto de horario',
                        'message' => 'Ya tienes una cita en ese horario'
                    ], 409);
                }
            }

            $entityManager->flush();

            return new JsonResponse($appointment->toArray());

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error al actualizar la cita: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'appointments_delete', methods: ['DELETE'])]
    public function deleteAppointment(Request $request, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $appointment = $entityManager->getRepository(Appointment::class)->find($id);
        if (!$appointment) {
            return new JsonResponse(['error' => 'Cita no encontrada'], 404);
        }

        if ($appointment->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'No autorizado'], 403);
        }

        try {
            $entityManager->remove($appointment);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Cita eliminada exitosamente']);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error al eliminar la cita: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/calendar/{year}/{month}', name: 'appointments_calendar', methods: ['GET'])]
    public function getMonthlyCalendar(Request $request, int $year, int $month, AppointmentRepository $appointmentRepository): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        if ($month < 1 || $month > 12) {
            return new JsonResponse(['error' => 'Mes inválido'], 400);
        }

        if ($year < 1970 || $year > 2100) {
            return new JsonResponse(['error' => 'Año inválido'], 400);
        }

        $appointments = $appointmentRepository->findByMonthAndUser($year, $month, $user);
        $stats = $appointmentRepository->getMonthlyStats($year, $month, $user);

        $result = [];
        foreach ($appointments as $appointment) {
            $result[] = $appointment->toCalendarArray();
        }

        return new JsonResponse([
            'year' => $year,
            'month' => $month,
            'appointments' => $result,
            'stats' => $stats
        ]);
    }

    #[Route('/calendar/{year}/{month}/full', name: 'appointments_calendar_full', methods: ['GET'])]
    public function getFullMonthlyCalendar(Request $request, int $year, int $month, AppointmentRepository $appointmentRepository): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        if ($month < 1 || $month > 12) {
            return new JsonResponse(['error' => 'Mes inválido'], 400);
        }

        if ($year < 1970 || $year > 2100) {
            return new JsonResponse(['error' => 'Año inválido'], 400);
        }

        $type = $request->query->get('type');
        $appointments = $appointmentRepository->findByMonthAndUser($year, $month, $user, $type);
        $stats = $appointmentRepository->getMonthlyStats($year, $month, $user);

        $result = [];
        foreach ($appointments as $appointment) {
            $result[] = $appointment->toArray();
        }

        // Group stats by type
        $statsByType = [
            'consulta' => 0,
            'ebbo' => 0,
            'iniciacion' => 0,
            'limpieza' => 0,
            'ceremonia' => 0
        ];

        foreach ($appointments as $appointment) {
            $appointmentType = strtolower($appointment->getType());
            if (isset($statsByType[$appointmentType])) {
                $statsByType[$appointmentType]++;
            }
        }

        return new JsonResponse([
            'year' => $year,
            'month' => $month,
            'appointments' => $result,
            'stats' => [
                'total' => count($result),
                'byType' => $statsByType
            ]
        ]);
    }

    #[Route('/extended', name: 'appointments_create_extended', methods: ['POST'])]
    public function createExtendedAppointment(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $data = json_decode($request->getContent(), true);
        
        // Validation
        $errors = [];
        if (!isset($data['clientName']) || empty($data['clientName'])) {
            $errors[] = 'El nombre del cliente es requerido';
        }
        if (!isset($data['date']) || empty($data['date'])) {
            $errors[] = 'La fecha es requerida';
        }
        if (!isset($data['time']) || empty($data['time'])) {
            $errors[] = 'La hora es requerida';
        }
        if (!isset($data['duration']) || !is_numeric($data['duration']) || $data['duration'] <= 0) {
            $errors[] = 'La duración debe ser un número válido mayor a 0';
        }

        if (!empty($errors)) {
            return new JsonResponse(['error' => 'Datos inválidos', 'details' => $errors], 400);
        }

        try {
            $appointment = new Appointment();
            $appointment->setUser($user);
            $appointment->setClientName($data['clientName']);
            $appointment->setType($data['type'] ?? 'consulta');
            $appointment->setDate(new \DateTime($data['date']));
            $appointment->setTime(new \DateTime($data['time']));
            $appointment->setDuration((int)$data['duration']);
            
            if (isset($data['title'])) {
                $appointment->setTitle($data['title']);
            }
            if (isset($data['status'])) {
                $appointment->setStatus($data['status']);
            }
            if (isset($data['notes'])) {
                $appointment->setNotes($data['notes']);
            }

            // Check for conflicts
            $appointmentRepository = $entityManager->getRepository(Appointment::class);
            $conflicts = $appointmentRepository->findConflictingAppointments(
                $user,
                $appointment->getDate(),
                $appointment->getTime(),
                $appointment->getDuration()
            );

            if (!empty($conflicts)) {
                return new JsonResponse([
                    'error' => 'Conflicto de horario',
                    'message' => 'Ya tienes una cita en ese horario'
                ], 409);
            }

            $entityManager->persist($appointment);
            $entityManager->flush();

            return new JsonResponse($appointment->toArray(), 201);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Error al crear la cita: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/day/{year}/{month}/{day}', name: 'appointments_day', methods: ['GET'])]
    public function getDayAppointments(Request $request, int $year, int $month, int $day, AppointmentRepository $appointmentRepository): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        if ($month < 1 || $month > 12) {
            return new JsonResponse(['error' => 'Mes inválido'], 400);
        }

        if ($day < 1 || $day > 31) {
            return new JsonResponse(['error' => 'Día inválido'], 400);
        }

        if ($year < 1970 || $year > 2100) {
            return new JsonResponse(['error' => 'Año inválido'], 400);
        }

        try {
            $date = new \DateTime("$year-$month-$day");
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Fecha inválida'], 400);
        }

        $appointments = $appointmentRepository->findByDateAndUser($date, $user);

        $result = [];
        foreach ($appointments as $appointment) {
            $appointmentData = $appointment->toArray();
            // Remove date field for day view to reduce redundancy
            unset($appointmentData['date']);
            unset($appointmentData['createdAt']);
            unset($appointmentData['updatedAt']);
            $result[] = $appointmentData;
        }

        return new JsonResponse([
            'date' => $date->format('Y-m-d'),
            'appointments' => $result,
            'totalAppointments' => count($result)
        ]);
    }

    #[Route('/types', name: 'appointments_types', methods: ['GET'])]
    public function getAppointmentTypes(Request $request): JsonResponse
    {
        try {
            $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $appointmentTypes = [
            [
                'id' => 'consulta',
                'name' => 'Consulta de Ifá',
                'color' => '#2E8B57',
                'defaultDuration' => 60,
                'description' => 'Consulta espiritual completa con Ifá'
            ],
            [
                'id' => 'ebbo',
                'name' => 'Ebbó',
                'color' => '#F5A623',
                'defaultDuration' => 120,
                'description' => 'Ceremonia de limpieza y purificación'
            ],
            [
                'id' => 'iniciacion',
                'name' => 'Iniciación',
                'color' => '#8B5CF6',
                'defaultDuration' => 480,
                'description' => 'Ceremonia de iniciación espiritual'
            ],
            [
                'id' => 'limpieza',
                'name' => 'Limpieza Espiritual',
                'color' => '#06B6D4',
                'defaultDuration' => 90,
                'description' => 'Sesión de limpieza energética'
            ],
            [
                'id' => 'ceremonia',
                'name' => 'Ceremonia',
                'color' => '#DC2626',
                'defaultDuration' => 180,
                'description' => 'Ceremonia espiritual general'
            ]
        ];

        return new JsonResponse([
            'appointmentTypes' => $appointmentTypes
        ]);
    }
} 