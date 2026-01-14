<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/functies', name: 'app_functies')]
    public function functies(): Response
    {
        return $this->render('home/functies.html.twig');
    }

    #[Route('/download', name: 'app_download')]
    public function download(): Response
    {
        return $this->render('home/download.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('contact/index.html.twig');
    }

    #[Route('/privacy', name: 'app_privacy')]
    public function privacy(): Response
    {
        return $this->render('home/privacy.html.twig');
    }

    #[Route('/algemene-voorwaarden', name: 'app_algemene_voorwaarden')]
    public function voorwaarden(): Response
    {
        return $this->render('home/voorwaarden.html.twig');
    }
}
