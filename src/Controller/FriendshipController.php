<?php

namespace App\Controller;

use App\Entity\Friendship;
use App\Entity\User;
use App\Form\UserSearchType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FriendshipController extends AbstractController
{

 #[Route(path:"/send-request/{username}", name:"send_request")]

public function sendFriendRequest(string $username,EntityManagerInterface $entityManager): Response
{
$user1 = $this->getUser();
$userRepository = $entityManager->getRepository(User::class);
$user2= $userRepository->findOneBy(['username' => $username]);

$existingFriendship = $entityManager->getRepository(Friendship::class)->findOneBy([
'user1' => $user1,
'user2' => $user2,
]);

if ($existingFriendship) {
return new Response('Friend request already sent or you are already friends.', 400);
}

$friendship = new Friendship();
$friendship->setUser1($user1);
$friendship->setUser2($user2);
$friendship->setStatus('pending');

$entityManager->persist($friendship);
$entityManager->flush();

return new Response('Friend request sent successfully!');
}

#[Route(path:"/accept-request/{username}", name:"accept_request")]
public function acceptFriendRequest(string $username, EntityManagerInterface $entityManager): Response
{
    $user2 = $this->getUser();
    $friendship = $entityManager->getRepository(Friendship::class)
        ->findOneBy([
            'user2' => $user2,
            'user1' => $entityManager->getRepository(User::class)->findOneByUsername($username),
            'status' => 'pending'
        ]);
    if (!$friendship) {
        return new Response('Friendship not found or request is not pending.', 400);
    }

    $friendship->setStatus('accepted');
    $entityManager->flush();

    return new Response('Friend request accepted!');
}

    #[Route(path:"/reject-request/{username}", name:"reject_request")]
    public function rejectFriendRequest(string $username, EntityManagerInterface $entityManager): Response
    {
        $user2 = $this->getUser(); // The current authenticated user

        // Fetch the friendship based on the username and current user
        $friendship = $entityManager->getRepository(Friendship::class)
            ->findOneBy([
                'user2' => $user2,
                'user1' => $entityManager->getRepository(User::class)->findOneByUsername($username),
                'status' => 'pending'
            ]);

        if (!$friendship) {
            return new Response('Friendship not found or request is not pending.', 400);
        }

        $entityManager->remove($friendship);
        $entityManager->flush();

        return new Response('Friend request rejected!');
    }

#[Route(path:"/friends", name:"friends")]
public function Friends(EntityManagerInterface $entityManager, Request $request, UserRepository $userRepository): Response
{   $user=$this->getUser();
    $repository = $entityManager->getRepository(Friendship::class);

    $queryBuilder = $repository->createQueryBuilder('f')
        ->where('f.user1 = :user')
        ->orWhere('f.user2 = :user')
        ->setParameter('user', $user);

    $friendships = $queryBuilder->getQuery()->getResult();
    $friends = [];
    $sentRequests = [];
    $receivedRequests = [];

    foreach ($friendships as $friendship) {
        if ($friendship->getStatus() === 'accepted') {
            $friends[] = $friendship;
        } elseif ($friendship->getStatus() === 'pending') {
            if ($friendship->getUser1() === $user) {
                $sentRequests[] = $friendship;
            } else {
                $receivedRequests[] = $friendship;
            }
        }
    }
    $form = $this->createForm(UserSearchType::class);
    $form->handleRequest($request);

    $users = [];

    if ($form->isSubmitted() && $form->isValid()) {
        $username = $form->get('username')->getData();
        $users = $userRepository->findBy(['username' => $username]);
    }


    return $this->render('friends/friends.html.twig', [
        'friends' => $friends,
        'sentRequests' => $sentRequests,
        'receivedRequests' => $receivedRequests,
        'users'=> $users,
        'form'=> $form
    ]);

}

}
