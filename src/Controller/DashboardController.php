<?php



namespace App\Controller;

use App\Entity\Game;
use App\Entity\Review;
use App\Entity\User;
use App\Entity\Wishlist;
use App\Form\GameType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;


class DashboardController extends AbstractController
{

    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(ManagerRegistry $doctrine): Response
    {   $user=$this->getUser();
        if(!$user)
        {
            return $this->redirectToRoute('app_login');
        }
        if (!in_array('ROLE_ADMIN', $this->getUser()->getRoles()))
            return $this->redirectToRoute('home');
        $entityManager = $doctrine->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        $totalUsers = $userRepository->count([]);
        return $this->render('admin/dashboard.html.twig', ['totalUsers' => $totalUsers,]);
    }
    #[Route('/admin/users', name: 'user_management')]
    public function manageUsers(ManagerRegistry $doctrine): Response
    {   $user=$this->getUser();
        if(!$user)
        {
            return $this->redirectToRoute('app_login');
        }
        if (!in_array('ROLE_ADMIN', $this->getUser()->getRoles()))
            return $this->redirectToRoute('home');
        $entityManager = $doctrine->getManager();
        $userRepository = $entityManager->getRepository(User::class);
        $users = $userRepository->findAll();
        return $this->render('admin/manage_users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/users/ban/{id}', name: 'user_ban')]
    public function banUsers(int $id,EntityManagerInterface $entityManager): Response
    {   $user=$this->getUser();
        if(!$user)
        {
            return $this->redirectToRoute('app_login');
        }
        if (!in_array('ROLE_ADMIN', $user->getRoles()))
            return $this->redirectToRoute('home');
        $userRepository = $entityManager->getRepository(User::class);
        $person=$userRepository->findOneBy(['id'=>$id]);
        if (!$person) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('user_management');
        }
        if(in_array('ROLE_ADMIN', $person->getRoles()))
        {
            $this->addFlash('error', 'You cannot ban this user.');
            return $this->redirectToRoute('user_management');
        }
        $person->setBanned(true);
        $entityManager->persist($person);
        $entityManager->flush();
        $this->addFlash('success', 'User successfully banned.');
        return $this->redirectToRoute('user_management');
    }

    #[Route('/admin/games', name: 'game_management')]
    public function manageGames(ManagerRegistry $doctrine): Response
    {
        $user=$this->getUser();
        if(!$user)
            return $this->redirectToRoute('app_login');
        if (!in_array('ROLE_ADMIN', $user->getRoles()))
            return $this->redirectToRoute('home');
        $entityManager = $doctrine->getManager();
        $gameRepository=$entityManager->getRepository(Game::class);
        $games = $gameRepository->findAll();
        return $this->render('admin/manage_games.html.twig', [
            'games' => $games,
        ]);
    }

    #[Route('/admin/game/edit/{id}', name: 'game_edit')]
    public function editGame(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $game = $entityManager->getRepository(Game::class)->find($id);

        if (!$game) {
            $this->addFlash('error', 'Game not found.');
            return $this->redirectToRoute('game_management');
        }

        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Game updated successfully.');
            return $this->redirectToRoute('game_management');
        }

        return $this->render('admin/edit_game.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/admin/upload-game', name: 'add_game')]
    public function addGame(Request $request, EntityManagerInterface $entityManager)
    {
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $entityManager->persist($game);
            $entityManager->flush();

            $this->addFlash('success', 'Game successfully uploaded!');

            return $this->redirectToRoute('game_management');
        }

        return $this->render('admin/add_game.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/reviews', name: 'review_management')]
    public function manageReviews(ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('home');
        }
        $entityManager = $doctrine->getManager();
        $reviewRepository = $entityManager->getRepository(Review::class);
        $reviews = $reviewRepository->findAll();
        return $this->render('admin/manage_reviews.html.twig', [
            'reviews' => $reviews,
        ]);
    }
    #[Route('/admin/review/delete/{id}', name: 'review_delete')]
    public function deleteReview(int $id, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('home');
        }

        $entityManager = $doctrine->getManager();
        $reviewRepository = $entityManager->getRepository(Review::class);
        $review = $reviewRepository->find($id);
        if (!$review) {
            $this->addFlash('error', 'Review not found.');
            return $this->redirectToRoute('review_management');
        }
        $entityManager->remove($review);
        $entityManager->flush();
        $this->addFlash('success', 'Review successfully deleted.');
        return $this->redirectToRoute('review_management');
    }

}