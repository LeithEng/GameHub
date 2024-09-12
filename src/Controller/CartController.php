<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    #[Route('/cart/add/{title}', name: 'add_cart')]
    public function addToCart(string $title, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $entityManager->persist($cart);
            $entityManager->flush();
        }

        $game = $entityManager->getRepository(Game::class)->findOneBy(['title'=>$title]);
        if (!$game) {
            return new Response('Game not found.', 404);
        }
        $existingCartItem = $entityManager->getRepository(CartItem::class)->findOneBy([
            'cart' => $cart,
            'game' => $game,
        ]);

        if ($existingCartItem) {
            return $this->redirectToRoute('cart', ['message' => 'Game already in the cart']);
        }

        $cartItem = new CartItem();
        $cartItem->setCart($cart);
        $cartItem->setGame($game);

        $entityManager->persist($cartItem);
        $entityManager->flush();

        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/remove/{title}', name: 'remove_cart')]
    public function removeFromCart(string $title, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);
        $game = $entityManager->getRepository(Game::class)->findOneBy(['title'=>$title]);
        $gameId = $game->getId();
        $cartItem =$entityManager->getRepository(CartItem::class)->findOneBy(['cart' => $cart, 'game' => $gameId]);
        $entityManager->remove($cartItem);
        $entityManager->flush();
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart', name: 'cart')]
    public function showCart(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);
        $id=$cart->getId();
        $items = $entityManager->getRepository(CartItem::class)->findBy(['cart' => $id]);
        $message = $request->query->get('message');

        return $this->render('cart/cart.html.twig', ['cart' => $cart, 'items' => $items,'message' => $message,]);
    }


}
