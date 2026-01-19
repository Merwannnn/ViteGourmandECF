<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeEditType;
use App\Form\CommandeType;
use App\Form\FiltreCommandeType;
use App\Repository\CommandeRepository;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/commande')]
final class CommandeController extends AbstractController
{
    #[IsGranted('ROLE_EMPLOYE')]
    #[Route(name: 'commande.index', methods: ['GET'])]
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
        }

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findAllWithUserAndMenuAndFilters($userName, $statut),
            'form' => $form
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/create/{id}', name: 'commande.create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, MenuRepository $repository, int $id, Security $security, CommandeRepository $commandeRepository): Response
    {
        $menu = $repository->find($id);
        $user = $security->getUser();

        if ($menu->getQuantiteRestante() <= 0) {
            $this->addFlash('danger', 'Ce menu n\'est plus disponible actuellement');
            return $this->redirectToRoute('menu.index');
        }

        $commande = new Commande();
        $commande->setNumeroCommande($this->generateNumeroCommande($commandeRepository));
        $commande->setStatut('Commande passée');
        $commande->setPretMateriel(0);
        $commande->setRestitutionMateriel(0);
        $commande->setDateCommande(new \DateTimeImmutable());
        $commande->setMenu($menu);
        $commande->setPrixMenu($menu->getPrixPersonne());
        $commande->setPrixLivraison(5.99);
        $commande->setUser($security->getUser());
        $form = $this->createForm(CommandeType::class, $commande, [
            'user' => $user
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $menu = $commande->getMenu();
            $nbPersonneMinimum = $menu->getNbPersonneMinimum();
            $nombrePersonne = $commande->getNombrePersonne();
            $prixLivraison = $commande->getPrixLivraison();
            $nbPersonneTotale = max($nbPersonneMinimum, $nombrePersonne);
            $prixTotaleCommande = $menu->getPrixPersonne() * $nbPersonneTotale + $prixLivraison;

            $commande->setPrixMenu($prixTotaleCommande);
            $menu->setQuantiteRestante($menu->getQuantiteRestante() - 1);

            $entityManager->persist($commande);
            $entityManager->flush();

            return $this->redirectToRoute('menu.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/create.html.twig', [
            'commande' => $commande,
            'form' => $form,
            'menu' => $menu,
            'user' => $user,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'commande.show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/edit', name: 'commande.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_EMPLOYE')) {
            if ($commande->getStatut() !== 'Commande passée') {
                $this->addFlash('error', 'Vous ne pouvez plus modifier cette commande');
                return $this->redirectToRoute('espace.show_commandes', [], Response::HTTP_SEE_OTHER);
            }
        }

        if ($this->isGranted('ROLE_EMPLOYE')) {
        $form = $this->createForm(CommandeEditType::class, $commande);
        } else {
        $form = $this->createForm(CommandeType::class, $commande, [
            'user' => $commande->getUser()
        ]);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $menu = $commande->getMenu();
            $nbPersonneMinimum = $menu->getNbPersonneMinimum();
            $nombrePersonne = $commande->getNombrePersonne();
            $prixLivraison = $commande->getPrixLivraison();
            $nbPersonneTotale = max($nbPersonneMinimum, $nombrePersonne);
            $prixTotaleCommande = $menu->getPrixPersonne() * $nbPersonneTotale + $prixLivraison;

            $commande->setPrixMenu($prixTotaleCommande);
            
            $entityManager->flush();

            if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_EMPLOYE')) {
                return $this->redirectToRoute('commande.index', [], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('espace.show_commandes', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'commande.delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->getPayload()->getString('_token'))) {

            if ($this->isGranted('ROLE_EMPLOYE')) {
                $motifAnnulation = $request->request->get('motifAnnulation');

                if (!$motifAnnulation) {
                    $this->addFlash('error', 'Vous devez obligatoirement donner un motif pour annuler une commande client');
                    return $this->redirectToRoute('commande.index', [], Response::HTTP_SEE_OTHER);
                }

                $user = $commande->getUser();
                $mail = (new TemplatedEmail())
                    ->to($user->getEmail())
                    ->from('no-reply@ViteEtGourmand.fr')
                    ->subject('Votre commande a été annuler')
                    ->htmlTemplate('emails/commande_annulation.html.twig')
                    ->context([
                        'commande' => $commande,
                        'motifAnnulation' => $motifAnnulation
                    ]);

                    $mailer->send($mail);
            }

            if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_EMPLOYE')) {
                if ($commande->getStatut() !== 'Commande passée') {
                    $this->addFlash('error', 'Vous ne pouvez plus annuler cette commande');
                    return $this->redirectToRoute('espace.show_commandes', [], Response::HTTP_SEE_OTHER);
                }
            }
            $entityManager->remove($commande);
            $entityManager->flush();

            if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_EMPLOYE')) {
                return $this->redirectToRoute('commande.index', [], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('espace.show_commandes', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->redirectToRoute('commande.index', [], Response::HTTP_SEE_OTHER);
    }

    private function generateNumeroCommande(CommandeRepository $commandeRepository) : string 
    {
        do {
            $numeroCommande = (string) random_int(10000000, 99999999);
        } while ($commandeRepository->findOneBy(['numeroCommande' => $numeroCommande]));

        return $numeroCommande;
    }
}
