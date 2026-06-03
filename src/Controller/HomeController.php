<?php

namespace App\Controller;

use App\Repository\AvisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    // cette fonction permet d'afficher la page d'accueil et les avis
    public function index(AvisRepository $repository): Response
    {
        return $this->render('home/index.html.twig', [
            // permet de récuperer et afficher les avis client
            'avis' => $repository->findAll(),
        ]);
    }
}
