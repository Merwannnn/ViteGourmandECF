<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Commande;
use App\Form\AvisType;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AvisController extends AbstractController
{
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
}