<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function landing(): Response
    {
        return $this->render('home/landing.html.twig');
    }

    #[Route('/features', name: 'app_features', methods: ['GET'])]
    public function features(): Response
    {
        return $this->render('home/features.html.twig');
    }
}

