<?php

namespace App\Controller;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Game;
use App\Entity\Library;
use App\Entity\User;
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

    #[Route('/cart/checkout', name: 'checkout')]
    public function checkout(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);

        if (!$cart) {
            return $this->redirectToRoute('cart', ['message' => 'Cart not found']);
        }

        $items = $entityManager->getRepository(CartItem::class)->findBy(['cart' => $cart]);

        $totalPrice = array_reduce($items, function ($carry, $item) {
            return $carry + $item->getGame()->getPrice();
        }, 0);

        return $this->render('cart/checkout.html.twig', [
            'cart' => $cart,
            'items' => $items,
            'totalPrice' => $totalPrice,
        ]);
    }

    #[Route('/cart/payment', name: 'payment')]
    public function payment(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);
        if (!$cart) {
            $this->addFlash('error', 'Cart not found');
            return $this->redirectToRoute('cart');
        }

        $library = $entityManager->getRepository(Library::class)->findOneBy(['user' => $user]);
        $items = $entityManager->getRepository(CartItem::class)->findBy(['cart' => $cart]);

        foreach ($items as $item) {
            $game = $item->getGame();
            if (!$library->getGames()->contains($game)) {
                $library->addGame($game);
            }
            $cart->removeCartItem($item);
            $entityManager->remove($item);

        }

        $entityManager->flush();

        return $this->redirectToRoute('payment_success');
    }
    #[Route('/payment/success', name: 'payment_success')]
    public function paymentSuccess(): Response
    {
        return $this->render('cart/payment_success.html.twig', [
            'message' => 'Payment successful! Thank you for your purchase.',
        ]);
    }

}
