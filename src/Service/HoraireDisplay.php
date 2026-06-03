<?php

namespace App\Service;

use App\Repository\HoraireRepository;
use Symfony\Component\HttpFoundation\Response;

class HoraireDisplay
{
    // cette fonction permet uniquement d'appeler le HoraireRepository pour l'utiliser dans le service par la suite
    public function __construct(private HoraireRepository $horaireRepository) {

    }

    // cette fonction permet uniquement d'afficher tout les horaire existant la ou elle est utiliser
    // dans notre cas elle n'est utiliser que dans le footer
    public function show()
    {
        return $this->horaireRepository->findAll();
    }
}