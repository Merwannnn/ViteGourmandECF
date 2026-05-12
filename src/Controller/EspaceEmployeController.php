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

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    #[IsGranted('ROLE_EMPLOYE')]
    #[Route('/espace-employe', name: 'espace_employe.index', methods: ['GET'])]
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

            return $this->render('commande/index.html.twig', [
                'commandes' => $commandeRepository->findAllWithUserAndMenuAndFilters($userName, $statut),
                'form' => $form
            ]);
        }

        return $this->render('espace_employe/index.html.twig', [
            'commandes' => $commandeRepository->findAllWithUserAndMenuAndFilters($userName, $statut),
            'form' => $form
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/espace-employe/comptes', name: 'espace_employe.indexComptes', methods: ['GET', 'POST'])]
    public function indexComptes(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UserRepository $userRepository) : Response
    {
        $user = new User();
        $form = $this->createForm(EmployeRegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $user->setRoles(["ROLE_EMPLOYE"]);
            $user->setName('');
            $user->setPhone('');
            $user->setAddress('');
            $user->setIsVerified(true);

            $entityManager->persist($user);
            $entityManager->flush();

            $mail = (new TemplatedEmail())
                ->from('support@ViteEtGourmand.fr')
                ->to((string) $user->getEmail())
                ->subject('Un compte employé a été créer pour vous')
                ->htmlTemplate('registration/employe_registration_email.html.twig');

                $this->mailer->send($mail);
        }

        return $this->render('espace_employe/indexComptes.html.twig', [
            'users' => $userRepository->findEmploye(),
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/espace-employe/comptes/{id}', name: 'userEmploye.delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager) : Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('espace_employe.indexComptes', [], Response::HTTP_SEE_OTHER);
    }
    
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/espace-employe/chiffre-affaire-menu', name: 'chiffre_affaire_menu.show', methods: ['GET', 'POST'])]
    public function chiffreAffaireMenu(Request $request, CommandeRepository $commandeRepository) : Response 
    {
        $chiffreAffaire = null;
        $menu = null;
        $dateStart = null;
        $dateEnd = null;

        $form = $this->createForm(FiltreChiffreAffaireMenuType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $menu = $form->get('menu')->getData();
            $dateStart = $form->get('dateStart')->getData();
            $dateEnd = $form->get('dateEnd')->getData();

            if ($dateEnd) {
                $dateEnd = $dateEnd->setTime(23, 59 ,59);
            }

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
