<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe
            if ($form->get('password')->getData()) {
                $user->setPassword($passwordHasher->hashPassword($user, $form->get('password')->getData()));
            }
            
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * Route pour que les utilisateurs ROLE_USER voient leur propre profil
     */
    #[Route('/show', name: 'app_user_show_self', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function showSelf(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir votre profil.');
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Route pour voir un utilisateur par ID
     * - Les utilisateurs avec uniquement ROLE_USER ne peuvent voir que leur propre profil
     * - Les autres rôles (ROLE_ADMIN, ROLE_COMPTABLE, etc.) peuvent voir tous les profils
     */
    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        $currentUser = $this->getUser();
        
        if (!$currentUser) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir un profil.');
        }

        // Vérifier si l'utilisateur a un rôle supérieur à ROLE_USER
        $hasAdminRole = $this->isGranted('ROLE_ADMIN');
        $hasComptableRole = $this->isGranted('ROLE_COMPTABLE');
        
        // Si l'utilisateur n'a pas de rôle supérieur (uniquement ROLE_USER), 
        // il ne peut voir que son propre profil
        if (!$hasAdminRole && !$hasComptableRole) {
            // Vérifier que l'utilisateur essaie de voir son propre profil
            if ($currentUser->getId() !== $user->getId()) {
                throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce profil. Vous pouvez uniquement voir votre propre profil via /user/show');
            }
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe seulement s'il a été modifié
            if ($form->get('password')->getData()) {
                $user->setPassword($passwordHasher->hashPassword($user, $form->get('password')->getData()));
            }
            
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
