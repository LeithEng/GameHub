<?php

namespace App\Controller;

use App\Entity\Friendship;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FriendshipController extends AbstractController
{

 #[Route(path:"/send-request/{username}", name:"send_request")]

public function sendFriendRequest(User $user2, EntityManagerInterface $entityManager): Response
{
$user1 = $this->getUser();

// Check if friendship already exists
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
public function acceptFriendRequest(Friendship $friendship, EntityManagerInterface $entityManager): Response
{

    if ($friendship->getUser2() !== $this->getUser() || $friendship->getStatus() !== 'pending') {
        return new Response('You cannot accept this request.', 400);
    }

    $friendship->setStatus('accepted');
    $entityManager->flush();

    return new Response('Friend request accepted!');
}

#[Route(path:"/reject-request/{username}", name:"reject_request")]

public function rejectFriendRequest(Friendship $friendship, EntityManagerInterface $entityManager): Response
{
    if ($friendship->getUser2() !== $this->getUser() || $friendship->getStatus() !== 'pending') {
        return new Response('You cannot reject this request.', 400);
    }

    $entityManager->remove($friendship);
    $entityManager->flush();

    return new Response('Friend request rejected!');
}

#[Route(path:"/friends", name:"friends")]
public function Friends(EntityManagerInterface $entityManager): Response
{   $user=$this->getUser();
    $friendships = $entityManager->getRepository(Friendship::class)->findBy(['user1' => $user]);
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

    return $this->render('friends/friends.html.twig', [
        'friends' => $friends,
        'sentRequests' => $sentRequests,
        'receivedRequests' => $receivedRequests,
    ]);

}

}
