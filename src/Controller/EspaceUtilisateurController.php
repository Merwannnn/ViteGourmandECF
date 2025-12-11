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
    #[IsGranted("ROLE_USER")]
    #[Route('/mon-espace', name: 'espace.index')]
    public function index(): Response
    {
        $user = $this->getUser();
        return $this->render('espace_utilisateur/index.html.twig', [
            'user' => $user
        ]);
    }

    #[IsGranted("ROLE_USER")]
    #[Route('/mon-espace/mes-commandes', name: 'espace.show_commandes')]
    public function showAllCommandes(CommandeRepository $repository): Response
    {
        $commandes = $repository->findMyCommand($this->getUser());
        return $this->render('espace_utilisateur/show_commandes.html.twig', [
            'commandes' => $commandes,
        ]);
        
    }

    #[IsGranted("USER_EDIT", subject: 'user')]
    #[Route('/mon-espace/{id}/modifier-mes-informations', name: 'espace.edit_infos', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager) : Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        
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
