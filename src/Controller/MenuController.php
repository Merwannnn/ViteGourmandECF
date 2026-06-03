<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Form\FiltreMenuType;
use App\Form\MenuType;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/menu')]
final class MenuController extends AbstractController
{
    #[Route(name: 'menu.index', methods: ['GET'])]
    // cette fonction permet de lister et d'afficher tout les menus et également d'utiliser des filtre pour les menus
    public function index(Request $request, MenuRepository $menuRepository): Response
    {
        $form = $this->createForm(FiltreMenuType::class);
        $form->handleRequest($request);

        // permet de récuperer les données des champs du formulaire de filtrage
        $filters = $form->getData() ?? [];
        // permet de récuperer les données de la variables utiliser pour la filtration(filters) du formulaire pour les passer au filtre dans le repository
        // et de stoker le tout dans une variable que l'on utilise ensuite
        $menu = $menuRepository->findByMenuAndThemeFilters($filters);

        // permet de vérifier que la requete reçu est une requete ajex(menu_filtre.js) et d'utiliser le template indiqué(fragment qui renvoie uniquement le ou les menu)
        // ce qui permet de mettre a jour les menu a l'aide des filtre sans recharger toute la page
        if ($request->isXmlHttpRequest()) {
            return $this->render('menu/menu.html.twig', [
                 'menus' => $menu,
            ]);
        }

        return $this->render('menu/index.html.twig', [
            'form' => $form,
            'menus' => $menu,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/create', name: 'menu.create', methods: ['GET', 'POST'])]
    // cette fonction permet a l'admin uniquement de créer un menu
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $menu = new Menu();
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        // permet de vérifier si le formulaire est corretement soumis et si il est valide avant d'enregistrer les données en base
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($menu);
            $entityManager->flush();

            return $this->redirectToRoute('menu.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('menu/create.html.twig', [
            'menu' => $menu,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'menu.show', methods: ['GET'])]
    // cette fonction permet d'afficher un menu avec tout les détail qui n'apparaissent pas dans l'index des menus
    public function show(Menu $menu): Response
    {
        return $this->render('menu/show.html.twig', [
            'menu' => $menu,
        ]);
    }

    #[IsGranted('ROLE_EMPLOYE')]
    #[Route('/{id}/edit', name: 'menu.edit', methods: ['GET', 'POST'])]
    // cette fonction permet de modifier un menu
    public function edit(Request $request, Menu $menu, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        // permet de vérifier si le formulaire est corretement soumis et si il est valide avant d'enregistrer les données en base
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('menu.index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('menu/edit.html.twig', [
            'menu' => $menu,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_EMPLOYE')]
    #[Route('/{id}', name: 'menu.delete', methods: ['POST'])]
    // cette focntion permet de supprimer un menu
    public function delete(Request $request, Menu $menu, EntityManagerInterface $entityManager): Response
    {
        // permet de vérifier si le token csrf est valide avant de supprimer le menu
        if ($this->isCsrfTokenValid('delete'.$menu->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($menu);
            $entityManager->flush();
        }

        return $this->redirectToRoute('menu.index', [], Response::HTTP_SEE_OTHER);
    }
}
