<?php

namespace App\Controller;

use App\Entity\CalendarEvent;
use App\Repository\CalendarEventRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/calendar')]
class CalendarController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CalendarEventRepository $calendarEventRepository;
    private AuthService $authService;

    public function __construct(
        EntityManagerInterface $entityManager,
        CalendarEventRepository $calendarEventRepository,
        AuthService $authService
    ) {
        $this->entityManager = $entityManager;
        $this->calendarEventRepository = $calendarEventRepository;
        $this->authService = $authService;
    }

    #[Route('/events/{year}/{month}', name: 'calendar_events_month', methods: ['GET'])]
    public function getEventsForMonth(Request $request, int $year, int $month): JsonResponse
    {
        $user = $this->authService->requireAuth($request);

        // Validate month
        if ($month < 1 || $month > 12) {
            return new JsonResponse(['error' => 'Mes inválido'], 400);
        }

        // Get type filter if provided
        $type = $request->query->get('type');
        if ($type && !in_array($type, CalendarEvent::getValidEventTypes())) {
            return new JsonResponse(['error' => 'Tipo de evento inválido'], 400);
        }

        // Get events for the month
        $events = $this->calendarEventRepository->findByUserAndMonth($user, $year, $month, $type);
        $stats = $this->calendarEventRepository->getMonthlyStatsByType($user, $year, $month);

        return new JsonResponse([
            'year' => $year,
            'month' => $month,
            'events' => array_map(fn($event) => $event->toArray(), $events),
            'stats' => [
                'total' => count($events),
                'byType' => $stats
            ]
        ]);
    }

    #[Route('/events', name: 'calendar_create_event', methods: ['POST'])]
    public function createEvent(Request $request): JsonResponse
    {
        $user = $this->authService->requireAuth($request);

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['error' => 'Datos JSON inválidos'], 400);
        }

        // Validate required fields
        $errors = [];
        if (empty($data['client'])) {
            $errors['client'] = 'El nombre del cliente es requerido';
        }
        if (empty($data['type']) || !in_array($data['type'], CalendarEvent::getValidEventTypes())) {
            $errors['type'] = 'Tipo de evento debe ser válido';
        }
        if (empty($data['date'])) {
            $errors['date'] = 'La fecha es requerida';
        }
        if (empty($data['time'])) {
            $errors['time'] = 'La hora es requerida';
        }

        if (!empty($errors)) {
            return new JsonResponse([
                'error' => 'Datos inválidos',
                'details' => $errors
            ], 400);
        }

        // Validate date format
        try {
            $date = new \DateTime($data['date']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Formato de fecha inválido'], 400);
        }

        // Validate time format (HH:MM)
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $data['time'])) {
            return new JsonResponse(['error' => 'Formato de hora inválido (HH:MM)'], 400);
        }

        // Set default duration if not provided
        $duration = $data['duration'] ?? $this->getDefaultDuration($data['type']);

        // Check for time conflicts
        $conflicts = $this->calendarEventRepository->findTimeConflicts(
            $user,
            $date,
            $data['time'],
            $duration
        );

        if (!empty($conflicts)) {
            return new JsonResponse([
                'error' => 'Conflicto de horario',
                'conflicts' => array_map(fn($event) => $event->toArray(), $conflicts)
            ], 409);
        }

        // Create new event
        $event = new CalendarEvent();
        $event->setUser($user);
        $event->setClient($data['client']);
        $event->setType($data['type']);
        $event->setDate($date);
        $event->setTime($data['time']);
        $event->setDuration($duration);
        $event->setNotes($data['notes'] ?? null);

        $this->calendarEventRepository->save($event, true);

        return new JsonResponse($event->toArray(), 201);
    }

    #[Route('/events/{eventId}', name: 'calendar_get_event', methods: ['GET'])]
    public function getEvent(Request $request, int $eventId): JsonResponse
    {
        $user = $this->authService->requireAuth($request);

        $event = $this->calendarEventRepository->findOneBy([
            'id' => $eventId,
            'user' => $user
        ]);

        if (!$event) {
            return new JsonResponse(['error' => 'Evento no encontrado'], 404);
        }

        return new JsonResponse($event->toArray());
    }

    #[Route('/events/{eventId}', name: 'calendar_update_event', methods: ['PUT'])]
    public function updateEvent(Request $request, int $eventId): JsonResponse
    {
        $user = $this->authService->requireAuth($request);

        $event = $this->calendarEventRepository->findOneBy([
            'id' => $eventId,
            'user' => $user
        ]);

        if (!$event) {
            return new JsonResponse(['error' => 'Evento no encontrado'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['error' => 'Datos JSON inválidos'], 400);
        }

        // Update fields if provided
        if (isset($data['client'])) {
            if (empty($data['client'])) {
                return new JsonResponse(['error' => 'El nombre del cliente no puede estar vacío'], 400);
            }
            $event->setClient($data['client']);
        }

        if (isset($data['type'])) {
            if (!in_array($data['type'], CalendarEvent::getValidEventTypes())) {
                return new JsonResponse(['error' => 'Tipo de evento inválido'], 400);
            }
            $event->setType($data['type']);
        }

        if (isset($data['date'])) {
            try {
                $date = new \DateTime($data['date']);
                $event->setDate($date);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Formato de fecha inválido'], 400);
            }
        }

        if (isset($data['time'])) {
            if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $data['time'])) {
                return new JsonResponse(['error' => 'Formato de hora inválido (HH:MM)'], 400);
            }
            $event->setTime($data['time']);
        }

        if (isset($data['duration'])) {
            if (!is_int($data['duration']) || $data['duration'] <= 0) {
                return new JsonResponse(['error' => 'Duración debe ser un número positivo'], 400);
            }
            $event->setDuration($data['duration']);
        }

        if (isset($data['notes'])) {
            $event->setNotes($data['notes']);
        }

        // Check for time conflicts (excluding current event)
        $conflicts = $this->calendarEventRepository->findTimeConflicts(
            $user,
            $event->getDate(),
            $event->getTime(),
            $event->getDuration(),
            $event->getId()
        );

        if (!empty($conflicts)) {
            return new JsonResponse([
                'error' => 'Conflicto de horario',
                'conflicts' => array_map(fn($conflictEvent) => $conflictEvent->toArray(), $conflicts)
            ], 409);
        }

        $event->setUpdatedAt(new \DateTime());
        $this->calendarEventRepository->save($event, true);

        return new JsonResponse($event->toArray());
    }

    #[Route('/events/{eventId}', name: 'calendar_delete_event', methods: ['DELETE'])]
    public function deleteEvent(Request $request, int $eventId): JsonResponse
    {
        $user = $this->authService->requireAuth($request);

        $event = $this->calendarEventRepository->findOneBy([
            'id' => $eventId,
            'user' => $user
        ]);

        if (!$event) {
            return new JsonResponse(['error' => 'Evento no encontrado'], 404);
        }

        $this->calendarEventRepository->remove($event, true);

        return new JsonResponse(['message' => 'Evento eliminado exitosamente']);
    }

    #[Route('/events/day/{year}/{month}/{day}', name: 'calendar_events_day', methods: ['GET'])]
    public function getEventsForDay(Request $request, int $year, int $month, int $day): JsonResponse
    {
        $user = $this->authService->requireAuth($request);

        // Validate date
        if (!checkdate($month, $day, $year)) {
            return new JsonResponse(['error' => 'Fecha inválida'], 400);
        }

        try {
            $date = new \DateTime(sprintf('%04d-%02d-%02d', $year, $month, $day));
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Fecha inválida'], 400);
        }

        $events = $this->calendarEventRepository->findByUserAndDate($user, $date);

        return new JsonResponse([
            'date' => $date->format('Y-m-d'),
            'events' => array_map(function ($event) {
                $eventData = $event->toArray();
                // Remove date field since it's already in the response
                unset($eventData['date']);
                return $eventData;
            }, $events),
            'totalEvents' => count($events)
        ]);
    }

    #[Route('/event-types', name: 'calendar_event_types', methods: ['GET'])]
    public function getEventTypes(Request $request): JsonResponse
    {
        $this->authService->requireAuth($request);

        return new JsonResponse([
            'eventTypes' => CalendarEvent::getEventTypes()
        ]);
    }

    /**
     * Get default duration for an event type
     */
    private function getDefaultDuration(string $type): int
    {
        $eventTypes = CalendarEvent::getEventTypes();
        foreach ($eventTypes as $eventType) {
            if ($eventType['id'] === $type) {
                return $eventType['defaultDuration'];
            }
        }
        return 60; // Default to 60 minutes
    }
} 