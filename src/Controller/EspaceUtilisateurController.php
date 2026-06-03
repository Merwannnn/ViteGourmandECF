<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\CommandeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class EspaceUtilisateurController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/mon-espace', name: 'espace.index')]
    // cette fonction permet d'afficher l'espace utilisateur
    public function index(): Response
    {
        // permet de récuperer l'utilisateur actuellement connecter pour pouvoir afficher ses information dans son espace ensuite
        $user = $this->getUser();
        return $this->render('espace_utilisateur/index.html.twig', [
            'user' => $user
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/mon-espace/mes-commandes', name: 'espace.show_commandes')]
    // cette fonction permet de lister toute les commandes d'un utilisateur pour qu'il puissent les consulté ensuite
    public function showAllCommandes(CommandeRepository $repository): Response
    {
        // permet de récuperer toute les commandes de l'utilisateur actuellement connecter via un filtre dans le repository
        $commandes = $repository->findMyCommand($this->getUser());
        return $this->render('espace_utilisateur/show_commandes.html.twig', [
            'commandes' => $commandes,
        ]);
        
    }

    #[IsGranted('USER_EDIT', subject: 'user')]
    #[Route('/mon-espace/{id}/modifier-mes-informations', name: 'espace.edit_infos', methods: ['GET', 'POST'])]
    // cette fonction permet a l'utilisateur de modifier ses informations personelle
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager) : Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        
        // permet de vérifier si le formulaire est corretement soumis et si il est valide avant d'enregistrer les données en base
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Vos informations ont bien été modifier');
            return $this->redirectToRoute('espace.index');
        }
        
        return $this->render('espace_utilisateur/edit.html.twig', [
            'user' => $user,
            'form' => $form
        ]);
    }
}
