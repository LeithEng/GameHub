<?php

namespace App\Controller;

use App\Entity\DebitCard;
use App\Form\DebitCardType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DebitCardController extends AbstractController
{
    #[Route('/debitcard/addfunds', name: 'add_funds_to_wallet')]
    public function addFundsToWallet(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DebitCardType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $number = $data->getNumber();
            $cvv = $data->getCvv();
            $card=$entityManager->getRepository(DebitCard::class)->findOneBy(['number'=> $number]);
            if (!$card|| $card->getCvv()!=$cvv)
            {
                $this->addFlash('error', 'Invalid credit card number or CVV .');
                return $this->redirectToRoute('add_funds_to_wallet');
            }
            $amount=$data->getBalance();
            if($amount>$card->getBalance())
            {
                $this->addFlash('error', 'Insufficient balance.');
                return $this->redirectToRoute('add_funds_to_wallet');
            }
            else{$user=$this->getUser();
                $wallet=$user->getWallet();
                $wallet->setBalance($wallet->getBalance()+$amount);
                $entityManager->persist($wallet);
                $card->setBalance($card->getBalance()-$amount);
                $entityManager->persist($card);
                $entityManager->flush();
                $this->addFlash('success', 'The specified amount was successfully added to your wallet!');
            }
        }

        return $this->render('debit_card/addfunds.html.twig', ['form' => $form->createView()]);
    }

}
