<?php

namespace App\Controller;

use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Game;

class GameController extends AbstractController
{
    #[Route('/game/{title}', name: 'app_game')]
    public function showGameDetails(string $title, EntityManagerInterface $entityManager): Response
    {

        $game = $entityManager->getRepository(Game::class)->findOneBy(['title' => $title]);

        if (!$game) {
            throw $this->createNotFoundException('The game does not exist.');
        }


        return $this->render('game/game.html.twig', [
            'game' => $game,
            'reviews' => $game->getReviews()
        ]);
    }
    #[Route('/search', name: 'search_games')]
    public function search(Request $request, GameRepository $gameRepository): Response
    {
        $query = $request->query->get('query');
        $games = [];

        if ($query) {
            $games = $gameRepository->findByTitle($query);
        }

        return $this->render('game/search.html.twig', [
            'games' => $games,
            'query' => $query,
        ]);
    }

}
