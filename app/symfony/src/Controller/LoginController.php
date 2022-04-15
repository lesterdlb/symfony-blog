<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    private AuthenticationUtils $authenticationUtils;
    private LoggerInterface $logger;

    public function __construct(AuthenticationUtils $authenticationUtils, LoggerInterface $logger)
    {
        $this->authenticationUtils = $authenticationUtils;
        $this->logger              = $logger;
    }

    #[Route('/{_locale}/login', name: 'app_login')]
    public function index(): Response
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();

        $lastUsername = $this->authenticationUtils->getLastUsername();

        if ($lastUsername) {
            $this->logger->info("User attempted login: ", ["lastUsername" => $lastUsername]);
        }

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout()
    {
    }
}
