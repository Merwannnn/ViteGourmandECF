<?php

namespace App\Service;

use App\Repository\HoraireRepository;
use Symfony\Component\HttpFoundation\Response;

class HoraireDisplay
{
    public function __construct(private HoraireRepository $horaireRepository) {

    }

    public function show()
    {
        return $this->horaireRepository->findAll();
    }
}