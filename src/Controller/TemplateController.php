<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TemplateController extends AbstractController
{
    #[Route('/template', name: 'app_template')]
    public function index(): Response
    {
        return $this->render('template/index.html.twig', [
            'controller_name' => 'TemplateController',
        ]);
    }
    #[Route('/test')]
    public function test(): Response
    {
        return new Response('Test Route');
    }
    #[Route('/front', name: 'front')]
    public function TempF(): Response
    {
        return $this->render('template/front/frontOffice.html.twig', [
            'controller_name' => 'TemplateController',
        ]);
    }
    #[Route('/back', name: 'back')]
    public function TempB(): Response
    {
        return $this->render('template/back.html.twig', [
            'controller_name' => 'TemplateController',
        ]);
    }
}
