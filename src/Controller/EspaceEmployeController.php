<?php

namespace App\Controller;

use App\Form\FiltreCommandeType;
use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class EspaceEmployeController extends AbstractController
{
    #[IsGranted('ROLE_EMPLOYE')]
    #[Route('/espace-employe', name: 'espace_employe.index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository, Request $request): Response
    {
        $userName = null;
        $statut = null;

        $form = $this->createForm(FiltreCommandeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $userName = $data['username'] ? $data['username'] : null;
            $statut = $data['statut'] ?? null;

            return $this->render('commande/index.html.twig', [
                'commandes' => $commandeRepository->findAllWithUserAndMenuAndFilters($userName, $statut),
                'form' => $form
            ]);
        }

        return $this->render('espace_employe/index.html.twig', [
            'commandes' => $commandeRepository->findAllWithUserAndMenuAndFilters($userName, $statut),
            'form' => $form
        ]);
    }
}
