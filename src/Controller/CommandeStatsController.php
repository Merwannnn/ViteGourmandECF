<?php

namespace App\Controller;

use App\Document\CommandeDocument;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommandeStatsController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/commandes-stats', name: 'commandes.stats')]
    // cette fonction permet de récuperer des données d'une collection MongoDB et d'afficher le nombre de commande par menu dans un graphique du template associé
    public function commandes(DocumentManager $dm): Response
    {
        // permet de trier les documents de la collection associé via le champs "menuName" et les comptes(par menu)
        $pipeline = [
            ['$group' => [
                '_id' => '$menuName',
                'total' => ['$sum' => 1]
            ]]
        ];

        // permet de récuperer les documents de la collection associé et de faire une agrégation MongoDB du pipeline par la suite
        $cursor = $dm->getDocumentCollection(CommandeDocument::class)
            ->aggregate($pipeline);

        // permet de convertire les données reçu du cursor en tableau pour pouvoir les passer au graphique ensuite
        $result = iterator_to_array($cursor);

        $menusTitle = [];
        $nbCommandes = [];

        // permet de récuperer les données du $result précédent et de les mettre dans les deux tableau créé précédemment pour les afficher dans le graphique par la suite
        foreach ($result as $row) {
            $menusTitle[] = $row['_id'];
            $nbCommandes[] = $row['total'];
        }

        return $this->render('commande_stats/index.html.twig', [
            'menusTitle' => $menusTitle,
            'nbCommandes' => $nbCommandes,
        ]);
    }
}
