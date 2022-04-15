<?php

namespace App\Controller;

use App\Config\Roles;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;

class RegistrationController extends AbstractController
{
    private FormLoginAuthenticator $formLoginAuthenticator;
    private UserAuthenticatorInterface $userAuthenticator;
    private UserPasswordHasherInterface $userPasswordHasher;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(
        FormLoginAuthenticator $formLoginAuthenticator,
        UserAuthenticatorInterface $userAuthenticator,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->formLoginAuthenticator = $formLoginAuthenticator;
        $this->userAuthenticator      = $userAuthenticator;
        $this->userPasswordHasher     = $userPasswordHasher;
        $this->entityManager          = $entityManager;
        $this->logger                 = $logger;
    }

    #[Route('/{_locale}/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setName($form->get('name')->getData());
            $user->setPassword(
                $this->userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setRoles([Roles::Editor->value]);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->logger->info(
                sprintf(
                    'A new user has been registered: %s.',
                    $user->getUserIdentifier()
                )
            );

            return $this->userAuthenticator->authenticateUser($user, $this->formLoginAuthenticator, $request);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
