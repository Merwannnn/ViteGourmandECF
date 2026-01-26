<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Commande;
use App\Form\AvisType;
use App\Form\EmployeAvisType;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AvisController extends AbstractController
{
    #[IsGranted('ROLE_EMPLOYE')]
    #[Route('/avis', name: 'avis.index', methods: ['GET'])]
    public function index(AvisRepository $avisRepository) : Response
    {
        return $this->render('avis/index.html.twig', [
            'avis' => $avisRepository->findAll()
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/avis/create/{id}', name: 'avis.create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, Commande $commande, AvisRepository $repository): Response
    {
        $user = $this->getUser();

        if ($commande->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if ($repository->findOneBy(['commande' => $commande, 'user' => $user])) {
            $this->addFlash('warning', 'Vous avez déja donner votre avis sur cette commande');
            return $this->redirectToRoute('menu.index');
        }

        $avis = new Avis;
        $avis->setCommande($commande);
        $avis->setUser($user);
        $avis->setStatut('Soumis');
        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($avis);
            $entityManager->flush();

            $this->addFlash('success', 'Votre avis a bien été pris en compte');
            return $this->redirectToRoute('espace.show_commandes', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('avis/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_EMPLOYE')]
    #[Route('/avis/{id}/edit', name: 'avis.edit', methods: ['GET', 'POST'])]
    public function edit(Avis $avis, EntityManagerInterface $entityManager) : Response
    {
        $avis->setStatut('Validé');
        $entityManager->flush();

        return $this->redirectToRoute('avis.index', [], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_EMPLOYE')]
    #[Route('/avis/{id}', name: 'avis.delete', methods: ['POST'])]
    public function delete(Request $request, Avis $avis, EntityManagerInterface $entityManager) : Response
    {
        if ($this->isCsrfTokenValid('delete'.$avis->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($avis);
            $entityManager->flush();
        }

        return $this->redirectToRoute('avis.index', [], Response::HTTP_SEE_OTHER);
    }
}