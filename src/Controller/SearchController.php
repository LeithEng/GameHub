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
    {   $user = $this->getUser();
        if(!$user)
            return $this->redirectToRoute('app_login');
        if(in_array('ROLE_ADMIN', $user->getRoles()))
        {
            return $this->redirectToRoute('admin_dashboard');
        }
        $query = $request->query->get('query');

        $games = $gameRepository->findByQuery($query);

        return $this->render('search/results.html.twig', [
            'games' => $games,
            'query' => $query
        ]);
    }
}
