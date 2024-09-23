<?php

namespace App\Controller;

use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(GameRepository $gameRepository): Response
    {   $user = $this->getUser();
        if(!$user)
            return $this->redirectToRoute('app_login');
        if(in_array('ROLE_ADMIN', $user->getRoles()))
        {
            return $this->redirectToRoute('admin_dashboard');
        }
        $randomGames = $gameRepository->findRandomGames(5);
        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
            'randomGames' => $randomGames,
        ]);
    }
}
