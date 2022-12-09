<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IvestigatorController extends AbstractController
{
    #[Route('/ivestigator', name: 'app_ivestigator')]
    public function index(): Response
    {
        return $this->render('ivestigator/index.html.twig', [
            'controller_name' => 'IvestigatorController',
        ]);
    }
    
}
