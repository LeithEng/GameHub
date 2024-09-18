<?php



namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

}