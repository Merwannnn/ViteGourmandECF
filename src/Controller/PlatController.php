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
    public function index(PlatRepository $platRepository) : Response
    {
        return $this->render('plat/index.html.twig', [
            'plats' => $platRepository->findAll()
        ]);
    }

    #[IsGranted('ROLE_EMPLOYE')]
    #[Route('/plat/create', name: 'plat.create', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $plat = new Plat();
        $form = $this->createForm(PlatType::class, $plat);
        $form->handleRequest($request);

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
    public function edit(Request $request, Plat $plat, EntityManagerInterface $entityManager) : Response
    {
        $form = $this->createForm(PlatType::class, $plat);
        $form->handleRequest($request);

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
    public function delete(Request $request, Plat $plat, EntityManagerInterface $entityManager) : Response
    {
        if ($this->isCsrfTokenValid('delete'.$plat->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($plat);
            $entityManager->flush();
        }

        return $this->redirectToRoute('plat.index', [], Response::HTTP_SEE_OTHER);
    }
}
