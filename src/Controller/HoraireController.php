<?php

namespace App\Controller;

use App\Entity\Horaire;
use App\Form\HoraireType;
use App\Repository\HoraireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class HoraireController extends AbstractController
{
    #[Route('/horaire', name: 'horaire.index')]
    public function index(HoraireRepository $horaireRepository): Response
    {
        return $this->render('horaire/index.html.twig', [
            'horairesIndex' => $horaireRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_EMPLOYE')]
    #[Route('/horaire/create', name: 'horaire.create', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager) : Response 
    {
        $horaire = new Horaire();
        $form = $this->createForm(HoraireType::class, $horaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($horaire);
            $entityManager->flush();

            return $this->redirectToRoute('espace_employe.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('horaire/create.html.twig', [
            'horaire' => $horaire,
            'form' => $form
        ]);
    }

    #[IsGranted('ROLE_EMPLOYE')]
    #[Route('/horaire/{id}/edit', name: 'horaire.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Horaire $horaire, EntityManagerInterface $entityManager) : Response 
    {
        $form = $this->createForm(HoraireType::class, $horaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('horaire.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('horaire/edit.html.twig', [
            'horaire' => $horaire,
            'form' => $form
        ]);
    }
}
