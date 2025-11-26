<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AlgmeenVoorwaardenController extends AbstractController
{
    #[Route('/voorwaarden', name: 'app_algmeen_voorwaarden')]
    public function index(): Response
    {
        return $this->render('algmeen_voorwaarden/index.html.twig', [
            'controller_name' => 'AlgmeenVoorwaardenController',
        ]);
    }
}
