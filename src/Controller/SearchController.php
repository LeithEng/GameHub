<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\GameRepository;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'search_games')]
    public function search(Request $request, GameRepository $gameRepository): Response
    {
        $query = $request->query->get('query');

        $games = $gameRepository->findByQuery($query);

        return $this->render('search/results.html.twig', [
            'games' => $games,
            'query' => $query
        ]);
    }
}
