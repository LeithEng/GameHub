<?php
namespace App\Controller;

use App\Entity\Library;
use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    public function showProfile(Request $request, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        $username = $user->getUsername();
        $email = $user->getEmail();
        return $this->render('profile/profile.html.twig', ['username' => $username, 'email' => $email]);
    }

    #[Route('/profile/change-password', name: 'change_pwd')]
    public function changePassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $current=$form->get('currentPassword')->getData();
            $new=$form->get('newPassword')->getData();
            if(!$passwordHasher->isPasswordValid($user, $current)) {
                $this->addFlash('danger', 'Your current password is incorrect.');
                return $this->redirectToRoute('change_pwd');
            }
            $hashedPassword=$passwordHasher->hashPassword($user, $new);
            $user->setPassword($hashedPassword);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Your password has been changed.');
            return $this->redirectToRoute('profile');
        }
        return $this->render('profile/change_password.html.twig', ['PasswordForm' => $form->createView()]);

    }


    #[Route('/profile/library', name: 'profile_library')]
    public function showLibrary(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $library = $entityManager->getRepository(Library::class)->findBy(['user' => $user]);
        $libraryGames=$library[0]->getGames();

        return $this->render('library/library.html.twig', [
            'libraryGames' => $libraryGames,
        ]);
    }

    #[Route('/profile/{id}', name: 'profile_page')]
    public function checkProfile($id, UserRepository $userRepository)
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $username=$user->getUsername();
        $email=$user->getEmail();

        return $this->render('profile/user_profile.html.twig', [
            'user' => $user,
            'username' => $username,
            'email' => $email,
        ]);
    }

}