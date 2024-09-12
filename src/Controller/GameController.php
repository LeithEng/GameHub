<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

}
