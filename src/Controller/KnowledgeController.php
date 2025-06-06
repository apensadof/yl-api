<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\KnowledgeRepository;
use App\Repository\KnowledgeCategoryRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/knowledge')]
class KnowledgeController extends AbstractController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    #[Route('/search', name: 'knowledge_search', methods: ['GET'])]
    public function search(Request $request, KnowledgeRepository $knowledgeRepository): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $query = $request->query->get('q');
        $category = $request->query->get('category', 'all');

        if (!$query) {
            return new JsonResponse(['error' => 'Parámetro de búsqueda requerido'], 400);
        }

        $results = $knowledgeRepository->search($query, $category);
        
        $searchResults = [];
        foreach ($results as $item) {
            // Calculate simple relevance based on title match
            $relevance = 0.5; // Base relevance
            if (stripos($item->getTitle(), $query) !== false) {
                $relevance += 0.3;
            }
            if (in_array(strtolower($query), array_map('strtolower', $item->getKeywords()))) {
                $relevance += 0.2;
            }
            
            $searchResults[] = $item->toSearchResult($relevance);
        }

        return new JsonResponse([
            'query' => $query,
            'category' => $category,
            'results' => $searchResults,
            'total' => count($searchResults)
        ]);
    }

    #[Route('/categories', name: 'knowledge_categories', methods: ['GET'])]
    public function getCategories(Request $request, KnowledgeCategoryRepository $categoryRepository): JsonResponse
    {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $categories = $categoryRepository->findAllWithCount();
        
        $result = [];
        foreach ($categories as $category) {
            if (is_array($category)) {
                // From query with count
                $result[] = $category[0]->toArray();
            } else {
                // Direct entity
                $result[] = $category->toArray();
            }
        }

        return new JsonResponse($result);
    }

    #[Route('/category/{categoryId}', name: 'knowledge_category_items', methods: ['GET'])]
    public function getCategoryItems(
        Request $request, 
        string $categoryId, 
        KnowledgeRepository $knowledgeRepository,
        KnowledgeCategoryRepository $categoryRepository
    ): JsonResponse {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $category = $categoryRepository->find($categoryId);
        if (!$category) {
            return new JsonResponse(['error' => 'Categoría no encontrada'], 404);
        }

        $page = max(1, (int)$request->query->get('page', 1));
        $perPage = min(50, max(1, (int)$request->query->get('perPage', 20)));

        $result = $knowledgeRepository->findByCategory($categoryId, $page, $perPage);
        
        $items = [];
        foreach ($result['items'] as $item) {
            $items[] = $item->toArray();
        }

        return new JsonResponse([
            'category' => $category->toArray(),
            'items' => $items,
            'pagination' => [
                'page' => $result['page'],
                'perPage' => $result['perPage'],
                'total' => $result['total'],
                'totalPages' => $result['totalPages']
            ]
        ]);
    }

    #[Route('/item/{categoryId}/{itemId}', name: 'knowledge_item_detail', methods: ['GET'])]
    public function getItemDetail(
        Request $request,
        string $categoryId,
        string $itemId,
        KnowledgeRepository $knowledgeRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $user = $this->authService->requireAuth($request);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Token inválido'], 401);
        }

        $item = $knowledgeRepository->findByCategoryAndId($categoryId, $itemId);
        if (!$item) {
            return new JsonResponse(['error' => 'Artículo no encontrado'], 404);
        }

        // Increment view count
        $item->incrementViews();
        $entityManager->persist($item);
        $entityManager->flush();

        return new JsonResponse($item->toDetailedArray());
    }
} 