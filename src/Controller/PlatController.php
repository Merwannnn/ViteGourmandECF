<?php

namespace App\Controller;

use App\Entity\Plat;
use App\Form\PlatType;
use App\Repository\PlatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PlatController extends AbstractController
{
    #[IsGranted('ROLE_EMPLOYE')]
    #[Route('/plat', name: 'plat.index', methods: ['GET'])]
    // cette fonction permet de lister et d'afficher tout les plats dans le template(plat/index.html.twig)
    public function index(PlatRepository $platRepository) : Response
    {
        return $this->render('plat/index.html.twig', [
            // permet de récuperer et d'afficher tout les plat
            'plats' => $platRepository->findAll()
        ]);
    }

    #[IsGranted('ROLE_EMPLOYE')]
    #[Route('/plat/create', name: 'plat.create', methods: ['GET', 'POST'])]
    // cette fonction permet de créer un plat
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $plat = new Plat();
        $form = $this->createForm(PlatType::class, $plat);
        $form->handleRequest($request);

        // permet de vérifier si le formulaire est corretement soumis et si il est valide avant d'enregistrer les données en base
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($plat);
            $entityManager->flush();

            return $this->redirectToRoute('plat.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('plat/create.html.twig', [
            'plat' => $plat,
            'form' => $form
        ]);
    }

    #[IsGranted('ROLE_EMPLOYE')]
    #[Route('/plat/{id}/edit', name: 'plat.edit', methods: ['GET', 'POST'])]
    // cette fonction permet de modifier un plat
    public function edit(Request $request, Plat $plat, EntityManagerInterface $entityManager) : Response
    {
        $form = $this->createForm(PlatType::class, $plat);
        $form->handleRequest($request);

        // permet de vérifier si le formulaire est corretement soumis et si il est valide avant d'enregistrer les données en base
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('plat.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('plat/edit.html.twig', [
            'plat' => $plat,
            'form' => $form
        ]);
    }

    #[IsGranted('ROLE_EMPLOYE')]
    #[Route('/plat/{id}', name: 'plat.delete', methods: ['POST'])]
    // cette fonction permet de supprimer un plat
    public function delete(Request $request, Plat $plat, EntityManagerInterface $entityManager) : Response
    {
        // permet de vérifier si le token csrf est valide avant de supprimer le menu
        if ($this->isCsrfTokenValid('delete'.$plat->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($plat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('plat.index', [], Response::HTTP_SEE_OTHER);
    }
}
