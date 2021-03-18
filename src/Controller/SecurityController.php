<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User;
        // On execute la méthode createForm() du SecurityController afin de créer un formulaire par rapport à la classe RegistrationFormType déstiné à remplir les setter de l'objet entité $user

        $formRegistration = $this->createForm(RegistrationFormType::class, $user, [
            'validation_groups' => ['registration']


        ]);
        // nous definisionns un groupe de validation de contrainte afin qu'il ne soit pas
        // pris en compte uniquement lors de l'inscirption et non lors de la modif
        // handleRequest() : méthode Symfony qui permet à la validation du formulaire, de remplir l'objet entity $user et d'envoyer les données du formulaire dans les bons setter et propriétés de l'entité $user

        $formRegistration->handleRequest($request);
        // SI le formulaire a bien été validé (isSubmitted) et que chaque donnée saisie ont bien été transmise aux bon setter de l'objet (isValid), alors on entre dans le IF

        if ($formRegistration->isSubmitted() && $formRegistration->isValid()) {

            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hash);
            $user->setRoles(["ROLE_USER"]);
            $manager->persist($user);
            $manager->flush();
            //dump($hash);
            $this->addFlash('success', "félicitations !! Votre compte a bien été validé ! vous pouvez dès à présent vous connecter");
            return $this->redirectToRoute('security_login');
        }
        return $this->render('security/registration.html.twig', [
            'formRegistration' => $formRegistration->createView() // on envoie le form sur le template
        ]);
    }
    /**
     * methode permettant de se connecter au blog
     * AuthenticationUtils permet de récupéer le dernier email saisie au moment de la connex 
     * et les messages d'erreur en cas de mauvaise connexion
     * @Route("/connexion", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [

            'error' => $error,
            'lastUsername' => $lastUsername
        ]);
    }

    /**
     * methode permettant de se connecter au blog
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout()
    {
    }
}
