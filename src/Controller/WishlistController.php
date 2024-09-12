<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Wishlist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WishlistController extends AbstractController
{
    #[Route('/wishlist', name: 'wishlist')]
    public function showWishlist(EntityManagerInterface $entityManager):Response
    {
        $user = $this->getUser();
        $wishlist = $entityManager->getRepository(Wishlist::class)->findBy(['user' => $user]);
        return $this->render('wishlist/index.html.twig', ['wishlists' => $wishlist]);
    }


    #[Route('/wishlist/add/{title}', name: 'add_wishlist')]

    public function addToWishlist(string $title, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $game = $entityManager->getRepository(Game::class)->findOneBy(['title' => $title]);


        if (!$game) {
            return new Response('Game not found.', 404);
        }

        $existingWishlist = $entityManager->getRepository(Wishlist::class)->findOneBy([
            'user' => $user,
            'game' => $game,
        ]);

        if ($existingWishlist) {
            return new Response('Game is already in your wishlist.', 400);
        }

        $wishlist = new Wishlist();
        $wishlist->setUser($user);
        $wishlist->setGame($game);
        $entityManager->persist($wishlist);
        $entityManager->flush();

        return $this->redirectToRoute('wishlist');
    }
    #[Route('/wishlist/remove/{title}', name: 'remove_wishlist')]
    public function removeFromWishlist(string $title, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $game = $entityManager->getRepository(Game::class)->findOneBy(['title' => $title]);

        if (!$game) {
            return new Response('Game not found.', 404);
        }

        $wishlist = $entityManager->getRepository(Wishlist::class)->findOneBy([
            'user' => $user,
            'game' => $game,
        ]);

        if (!$wishlist) {
            return new Response('Game not found in your wishlist.', 404);
        }

        $entityManager->remove($wishlist);
        $entityManager->flush();

        return $this->redirectToRoute('wishlist');
    }



}
