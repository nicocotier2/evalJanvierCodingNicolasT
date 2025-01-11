<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\BookRepository;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(BookRepository $bookRepository,Request $request,  UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Créer un nouvel utilisateur
        $user = new User();

        // Créer le formulaire d'inscription
        $form = $this->createForm(RegistrationFormType::class, $user);
        $books = $bookRepository->findAll();
        // Traiter le formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encoder le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);

            // Enregistrer l'utilisateur dans la base de données
            //$entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // Ajouter un message de succès
            $this->addFlash('success', 'Your account has been created!');

            // Rediriger l'utilisateur vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/index.html.twig', [
            'registrationForm' => $form->createView(),
            'books' => $books,
        ]);
    }
}
