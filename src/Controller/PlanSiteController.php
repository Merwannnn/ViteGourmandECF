<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PlanSiteController extends AbstractController
{
    #[Route('/plan-site', name: 'app.plan_site')]
    public function index(MenuRepository $repository): Response
    {
        return $this->render('plan_site/index.html.twig', [
            'menus' => $repository->findAll()
        ]);
    }
}
