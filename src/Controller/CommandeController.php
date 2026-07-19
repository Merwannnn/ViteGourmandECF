<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeEditType;
use App\Form\CommandeType;
use App\Form\FiltreCommandeType;
use App\Repository\CommandeRepository;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
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
    // cette fonction permet de lister et/ou filtrer toute les commande client et n'est accessible qu'au role "ROLE_EMPLOYE"
    public function index(CommandeRepository $commandeRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $userName = null;
        $statut = null;
        $page = $request->query->getInt('page', 1);

        $form = $this->createForm(FiltreCommandeType::class);
        $form->handleRequest($request);

        // permet de vérifier si le formulaire est corretement soumis et si il est valide avant de filtrer les données
        if ($form->isSubmitted() && $form->isValid()) {
            // permet de récuperer les données des champs du formulaire de filtrage
            $data = $form->getData();
            // permet d'enregistrer les données précédemment récuperer dans les variable associé pour pouvoir effectuer le filtrage
            $userName = $data['username'] ?? null;
            $statut = $data['statut'] ?? null;
        }
        
        $queryBuilder = $commandeRepository->findAllWithUserAndMenuAndFilters($userName, $statut);
        
        // permet de paginer les résultat du queryBuilder associé(utilise le bundle KnpPaginatorBundle)
        // les paramètre(queryBuilder, page et 10) correspondent a notre queryBuilder au numéro de page en cours et a la limite de résultat par page
        $pagination = $paginator->paginate(
            $queryBuilder,
            $page,
            10
        );

        return $this->render('commande/index.html.twig', [
            // permet de récupérer la variable qui permet de récuperer les données des variables utiliser pour la filtration 
            // et également de paginer les résultat pour les afficher
            'commandes' => $pagination,
            'form' => $form
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/create/{id}', name: 'commande.create', methods: ['GET', 'POST'])]
    // cette fonction permet de créer une commande client
    public function create(Request $request, EntityManagerInterface $entityManager, MenuRepository $repository, int $id, Security $security, CommandeRepository $commandeRepository): Response
    {
        // permet d'empécher les utilisateur ayant un certain role d'accéder a cette page en les redirigant 
        if ($this->isGranted("ROLE_EMPLOYE") || $this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('menu.index', [], Response::HTTP_SEE_OTHER);
        }

        $menu = $repository->find($id);
        // permet de récuperer l'utilisateur connecté pour des raisons de sécurité
        $user = $security->getUser();

        // permet de vérifier si le menu concerné a du stock et redirige l'utilisateur sinon
        if ($menu->getQuantiteRestante() <= 0) {
            // permet d'afficher un message a l'utilisateur si il n'ya plus de stock
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

        // permet de vérifier si le formulaire est corretement soumis et si il est valide avant d'enregistrer les données en base
        if ($form->isSubmitted() && $form->isValid()) {
            $menu = $commande->getMenu();
            $nbPersonneMinimum = $menu->getNbPersonneMinimum();
            $nombrePersonne = $commande->getNombrePersonne();
            $prixLivraison = $commande->getPrixLivraison();

            // permet de récuperer le prix final de la commande
            // permet de vérifier si le nombrePersonne indiqué est bien supérieur au nbPersonneMinimum
            $nbPersonneTotale = max($nbPersonneMinimum, $nombrePersonne);
            $prixTotaleCommande = $menu->getPrixPersonne() * $nbPersonneTotale;

            // permet d'ajouter une promotion de 10% au prix final si les conditions sont respecté
            if ($nbPersonneTotale >= $menu->getNbPersonneMinimum() + 5) {
                $prixTotaleCommande *= 0.9;
            }
            $prixTotaleCommande += $prixLivraison;

            $commande->setPrixMenu($prixTotaleCommande);
            // permet de réduire la quantité restante d'un menu quand ce menu est commandé
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
    #[Route('/{id}/edit', name: 'commande.edit', methods: ['GET', 'POST'])]
    // cette fonction permet a un utilisateur possédant le role "ROLE_USER" de mettre a jour une commande
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        // permet d'empécher la modfication d'une commande par un utilisateur possédant le role "ROLE_USER" si sont statut n'est pas "Commande passée"
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_EMPLOYE')) {
            if ($commande->getStatut() !== 'Commande passée') {
                $this->addFlash('error', 'Vous ne pouvez plus modifier cette commande');
                return $this->redirectToRoute('espace.show_commandes', [], Response::HTTP_SEE_OTHER);
            }
        }

        // permet uniquement de rediriger l'utilsateur vers le bon formType en fonction de son role
        if ($this->isGranted('ROLE_EMPLOYE')) {
        $form = $this->createForm(CommandeEditType::class, $commande);
        } else {
        $form = $this->createForm(CommandeType::class, $commande, [
            'user' => $commande->getUser()
        ]);
        }
        $form->handleRequest($request);

        // permet de vérifier si le formulaire est corretement soumis et si il est valide avant d'enregistrer les données en base
        if ($form->isSubmitted() && $form->isValid()) {
            $menu = $commande->getMenu();
            $nbPersonneMinimum = $menu->getNbPersonneMinimum();
            $nombrePersonne = $commande->getNombrePersonne();
            $prixLivraison = $commande->getPrixLivraison();
            // permet de récuperer le prix final de la commande
            // identique au code de calcul du prix final de la fonction create
            $nbPersonneTotale = max($nbPersonneMinimum, $nombrePersonne);
            $prixTotaleCommande = $menu->getPrixPersonne() * $nbPersonneTotale;

            if ($nbPersonneTotale >= $menu->getNbPersonneMinimum() + 5) {
                $prixTotaleCommande *= 0.9;
            }
            $prixTotaleCommande += $prixLivraison;

            $commande->setPrixMenu($prixTotaleCommande);
            
            $entityManager->flush();

            // permet uniquement de rediriger l'utilsateur vers la bonne page en fonction de son role
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
    // cette fonction permet de supprimer une commande
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        // permet de vérifier si le token csrf est valide avant de supprimer la commande
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->getPayload()->getString('_token'))) {

            // permet a un utilisateur possédant le role "ROLE_EMPLOYE" de supprimer une commande si il indique un motif
            if ($this->isGranted('ROLE_EMPLOYE')) {
                // permet de récuperer le motif de suppression dans le champ motifAnnulation dans le delete form correspondant
                $motifAnnulation = $request->request->get('motifAnnulation');

                // permet de vérifier si il ya bien en motif de suppression indiqué et ajoute une message d'erreur sinon
                if (!$motifAnnulation) {
                    $this->addFlash('error', 'Vous devez obligatoirement donner un motif pour annuler une commande client');
                    return $this->redirectToRoute('commande.index', [], Response::HTTP_SEE_OTHER);
                }

                // permet d'envoyer un mail qui contient le motif de supression a l'utilisateur qui a créer la commande 
                $user = $commande->getUser();
                try {
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
                } catch (\Exception $e) {
                    // permet d'informer l'utilisateur qui il y a eu une erreur
                    $this->addFlash('danger', 'Impossible d\'envoyer le mail');
                }
            }

            // permet d'empécher toute utilisateur ne possédant pas le role "ROLE_EMPLOYE" ou "ROLE_ADMIN" de supprimer une commande si sont statut n'est pas "Commande passée"
            if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_EMPLOYE')) {
                if ($commande->getStatut() !== 'Commande passée') {
                    // permet d'afficher un message a l'utilisateur si le statut de la commande n'est pas "Commande passée"
                    $this->addFlash('error', 'Vous ne pouvez plus annuler cette commande');
                    return $this->redirectToRoute('espace.show_commandes', [], Response::HTTP_SEE_OTHER);
                }
            }
            $entityManager->remove($commande);
            $entityManager->flush();

            // permet uniquement de rediriger l'utilsateur vers la bonne page en fonction de son role
            if ($this->isGranted('ROLE_ADMIN') || $this->isGranted('ROLE_EMPLOYE')) {
                return $this->redirectToRoute('commande.index', [], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('espace.show_commandes', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->redirectToRoute('commande.index', [], Response::HTTP_SEE_OTHER);
    }

    // cette fonction permet uniquement de génerer un numéro de commande
    private function generateNumeroCommande(CommandeRepository $commandeRepository) : string
    {
        do {
            // génere un numero de commande de 8 chiffre
            $numeroCommande = (string) random_int(10000000, 99999999);
            // verifie que le numero de commande n'existe pas 
        } while ($commandeRepository->findOneBy(['numeroCommande' => $numeroCommande]));

        return $numeroCommande;
    }
}
