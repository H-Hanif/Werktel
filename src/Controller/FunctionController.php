<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FunctionController extends AbstractController
{
    #[Route('/function', name: 'app_function')]
    public function index(): Response
    {
        return $this->render('function/index.html.twig', [
            'controller_name' => 'FunctionController',
        ]);
    }
}
