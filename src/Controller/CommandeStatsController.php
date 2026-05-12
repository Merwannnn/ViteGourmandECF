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
    public function commandes(DocumentManager $dm): Response
    {
        $pipeline = [
            ['$group' => [
                '_id' => '$menuName',
                'total' => ['$sum' => 1]
            ]]
        ];

        $cursor = $dm->getDocumentCollection(CommandeDocument::class)
            ->aggregate($pipeline);

        $result = iterator_to_array($cursor);

        $menusTitle = [];
        $nbCommandes = [];

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
