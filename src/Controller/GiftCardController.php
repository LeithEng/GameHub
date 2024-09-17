<?php

namespace App\Controller;

use App\Entity\GiftCard;
use App\Form\GiftCardType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GiftCardController extends AbstractController
{
    #[Route('/redeem-gift-card', name: 'redeem_gift_card')]
    public function redeemGiftCard(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GiftCardType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $code = $data['code'];

            $giftCard = $entityManager->getRepository(GiftCard::class)->findOneBy(['code' => $code]);

            if ($giftCard && $giftCard->isValid()) {
                $user = $this->getUser();
                $user->addFunds($giftCard->getAmount());
                $giftCard->setValid(false);
                $entityManager->persist($giftCard);
                $entityManager->flush();
                $this->addFlash('success', 'Gift card redeemed successfully!');
            } else {
                $this->addFlash('error', 'Invalid or already redeemed gift card.');
            }
        }

        return $this->render('gift_card/redeem.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
