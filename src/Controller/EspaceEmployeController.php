<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmployeRegistrationType;
use App\Form\FiltreChiffreAffaireMenuType;
use App\Form\FiltreCommandeType;
use App\Repository\CommandeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class EspaceEmployeController extends AbstractController
{
    private MailerInterface $mailer;

    // permet uniquement d'appeler la MailerInterface pour l'utiliser dans le controller par la suite
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    #[IsGranted('ROLE_EMPLOYE')]
    #[Route('/espace-employe', name: 'espace_employe.index', methods: ['GET'])]
    // cette fonction permet initialement d'afficher l'espace employe mais également d'utiliser des filtre spécifique pour les commandes client
    public function index(CommandeRepository $commandeRepository, Request $request): Response
    {
        $userName = null;
        $statut = null;

        $form = $this->createForm(FiltreCommandeType::class);
        $form->handleRequest($request);

        // permet de vérifier si le formulaire est corretement soumis et si il est valide avant de filtrer les données
        if ($form->isSubmitted() && $form->isValid()) {
            // permet de récuperer les données des champs du formulaire de filtrage
            $data = $form->getData();
            // permet d'enregistrer les données précédemment récuperer dans les variable associé pour pouvoir effectuer le filtrage
            $userName = $data['username'] ?? null;
            $statut = $data['statut'] ?? null;

            return $this->render('commande/index.html.twig', [
                // permet de récuperer les données des variables utiliser pour la filtration(userName, statut) du formulaire pour les passer au filtre dans le repository
                'commandes' => $commandeRepository->findAllWithUserAndMenuAndFilters($userName, $statut),
                'form' => $form
            ]);
        }

        return $this->render('espace_employe/index.html.twig', [
            // permet de récuperer les données des variables utiliser pour la filtration(userName, statut) du formulaire pour les passer au filtre dans le repository
            'commandes' => $commandeRepository->findAllWithUserAndMenuAndFilters($userName, $statut),
            'form' => $form
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/espace-employe/comptes', name: 'espace_employe.indexComptes', methods: ['GET', 'POST'])]
    // cette fonction permet a l'administrateur uniquement de créer des comptes pour les employe et de les afficher
    public function indexComptes(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UserRepository $userRepository) : Response
    {
        $user = new User();
        $form = $this->createForm(EmployeRegistrationType::class, $user);
        $form->handleRequest($request);

        // permet de vérifier si le formulaire est corretement soumis et si il est valide avant de créer le compte employe
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // permet de hasher(cryptée) le mot de passe indiqué dans plainPassword
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // permet de donner le role "ROLE_EMPLOYE" a l'utilisateur créer
            $user->setRoles(["ROLE_EMPLOYE"]);
            $user->setName('');
            $user->setPhone('');
            $user->setAddress('');
            // permet de vérifier automatiquement l'employe sans passer par un mail de vérification
            $user->setIsVerified(true);

            $entityManager->persist($user);
            $entityManager->flush();

            // permet d'envoyer un mail a l'employe en fois son compte créer
            $mail = (new TemplatedEmail())
                ->from('support@ViteEtGourmand.fr')
                ->to((string) $user->getEmail())
                ->subject('Un compte employé a été créer pour vous')
                ->htmlTemplate('registration/employe_registration_email.html.twig');

                $this->mailer->send($mail);
        }

        return $this->render('espace_employe/indexComptes.html.twig', [
            // permet de filtrer les utilisteur afficher dans l'espace des comptes employe via leur role
            'users' => $userRepository->findEmploye(),
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/espace-employe/comptes/{id}', name: 'userEmploye.delete', methods: ['POST'])]
    // cette fonction permet a l'administrateur uniquement de supprimer des comptes employe
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager) : Response
    {
        // permet de vérifier si le token csrf est valide avant de supprimer le compte employe
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('espace_employe.indexComptes', [], Response::HTTP_SEE_OTHER);
    }
    
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/espace-employe/chiffre-affaire-menu', name: 'chiffre_affaire_menu.show', methods: ['GET', 'POST'])]
    // cette fonction permet d'afficher le chiffre d'affaire en filtrant par menu et par date
    public function chiffreAffaireMenu(Request $request, CommandeRepository $commandeRepository) : Response 
    {
        $chiffreAffaire = null;
        $menu = null;
        $dateStart = null;
        $dateEnd = null;

        $form = $this->createForm(FiltreChiffreAffaireMenuType::class);
        $form->handleRequest($request);

        // permet de vérifier si le formulaire est corretement soumis et si il est valide avant de filtrer les données
        if ($form->isSubmitted() && $form->isValid()) {
            // permet de récuperer les données des champs du formulaire de filtrage
            $data = $form->getData();
            // permet d'enregistrer les données précédemment récuperer dans les variable associé pour pouvoir effectuer le filtrage
            $menu = $data['menu'];
            $dateStart = $data['dateStart'];
            $dateEnd = $data['dateEnd'];

            // permet de mettre l'heure de récuperation des données de dateEnd a 23h59(par défault en php le temps est 00h) pour ne pas exclure les données du jour qu'on a mis en dateEnd
            if ($dateEnd) {
                $dateEnd = $dateEnd->setTime(23, 59 ,59);
            }

            // permet de récuperer les données des variables utiliser pour la filtration(menu, dateStart, dateEnd) du formulaire pour les passer au filtre dans le repository
            $chiffreAffaire = $commandeRepository->findMenuSumAndFilters($menu, $dateStart, $dateEnd);
        }

        return $this->render('espace_employe/chiffre_affaire_menu.html.twig', [
            'form' => $form,
            'chiffreAffaire' => $chiffreAffaire,
            'menu' => $menu,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd
        ]);
    }
}
