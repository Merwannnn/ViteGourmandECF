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
    // cette fonction permet de lister tout les avis client
    public function index(AvisRepository $avisRepository) : Response
    {
        // permet de récupérer et d'afficher tout les avis dans le template
        return $this->render('avis/index.html.twig', [
            'avis' => $avisRepository->findAll()
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/avis/create/{id}', name: 'avis.create', methods: ['GET', 'POST'])]
    // cette fonction permet de créer un avis pour une commande client
    public function create(Request $request, EntityManagerInterface $entityManager, Commande $commande, AvisRepository $repository): Response
    {
        $user = $this->getUser();

        // permet de vérifier si l'utilisateur qui a créer la commande est bien celui qui est connecté avant de créer un avis
        if ($commande->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        // permet de vérifier si il éxiste déja un avis pour la commande de l'utilsateur et le redirige si c'est le cas
        if ($repository->findOneBy(['commande' => $commande, 'user' => $user])) {
            $this->addFlash('warning', 'Vous avez déja donner votre avis sur cette commande');
            return $this->redirectToRoute('espace.show_commandes');
        }

        $avis = new Avis;
        $avis->setCommande($commande);
        $avis->setUser($user);
        $avis->setStatut('Soumis');
        $form = $this->createForm(AvisType::class, $avis);
        $form->handleRequest($request);

        // permet de vérifier si le formulaire est corretement soumis et si il est valide avant d'enregistrer les données en base
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
    // cette fonction permet de mettre a jour un avis dans ce cas elle sert uniquement a le valider
    public function edit(Avis $avis, EntityManagerInterface $entityManager) : Response
    {
        $avis->setStatut('Validé');
        $entityManager->flush();

        return $this->redirectToRoute('avis.index', [], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_EMPLOYE')]
    #[Route('/avis/{id}', name: 'avis.delete', methods: ['POST'])]
    // cette fonction permet de supprimer un avis
    public function delete(Request $request, Avis $avis, EntityManagerInterface $entityManager) : Response
    {
        // permet de vérifier si le token csrf est valide avant de supprimer l'avis
        if ($this->isCsrfTokenValid('delete'.$avis->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($avis);
            $entityManager->flush();
        }

        return $this->redirectToRoute('avis.index', [], Response::HTTP_SEE_OTHER);
    }
}