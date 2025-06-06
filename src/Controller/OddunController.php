<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Oddun;
use App\Entity\ComplementoOddun;

class OddunController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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
} 