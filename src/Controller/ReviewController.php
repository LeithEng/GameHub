<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Review;
use App\Form\ReviewType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReviewController extends AbstractController
{
    #[Route('/game/{title}/add-review', name: 'add_review')]
    public function addReview(string $title, Request $request, EntityManagerInterface $entityManager): Response
    {   $game=$entityManager->getRepository(Game::class)->findOneBy(['title'=>$title]);
        $user = $this->getUser();

        $existingReview = $entityManager->getRepository(Review::class)->findOneBy([
            'user' => $user,
            'game' => $game,
        ]);

        if ($existingReview) {
            return new Response('You have already reviewed this game.', 400);
        }

        $review = new Review();
        $review->setGame($game);
        $review->setUser($user);
        $review->setCreatedAt(new \DateTimeImmutable());
        $review->setUpdatedAt(new \DateTimeImmutable());

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($review);
            $entityManager->flush();

            return $this->redirectToRoute('app_game', ['title' => $game->getTitle()]);
        }

        return $this->render('review/add_review.html.twig', [
            'form' => $form->createView(),
            'game' => $game,
        ]);
    }

    #[Route('/game/{title}/review/{id}/edit', name: 'edit_review')]
    public function editReview(string $title, int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $game = $entityManager->getRepository(Game::class)->findOneBy(['title' => $title]);
        $review = $entityManager->getRepository(Review::class)->find($id);

        if (!$review || $review->getUser() !== $this->getUser()) {
            return new Response('Review not found or you do not have permission to edit this review.', 403);
        }

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $review->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->redirectToRoute('app_game', ['title' => $game->getTitle()]);
        }

        return $this->render('review/add_review.html.twig', [
            'form' => $form->createView(),
            'game' => $game,
        ]);
    }


    #[Route('/game/{title}/review/{id}/delete', name: 'delete_review')]
    public function deleteReview(string $title, int $id, EntityManagerInterface $entityManager): Response
    {
        $review = $entityManager->getRepository(Review::class)->find($id);

        if (!$review || $review->getUser() !== $this->getUser()) {
            return new Response('Review not found or you do not have permission to delete this review.', 403);
        }

        $entityManager->remove($review);
        $entityManager->flush();

        return $this->redirectToRoute('app_game', ['title' => $title]);
    }


}
