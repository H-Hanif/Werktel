<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PartnerWordenController extends AbstractController
{
    #[Route('/partner/worden', name: 'app_partner_worden')]
    public function index(): Response
    {
        return $this->render('partner_worden/index.html.twig', [
            'controller_name' => 'PartnerWordenController',
        ]);
    }
}
