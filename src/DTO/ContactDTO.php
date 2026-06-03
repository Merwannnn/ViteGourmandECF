<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

// cette class(DTO) permet de correctement valider et transmettre les données reçu du formulaire(ContactType)
class ContactDTO
{

    #[Assert\NotBlank()]
    #[Assert\Length(min: 2, max: 100)]
    public string $name = '';
    
    #[Assert\NotBlank()]
    #[Assert\Email()]
    public string $email = '';
    
    #[Assert\NotBlank()]
    #[Assert\Length(min: 10)]
    public string $message = '';

    #[Assert\NotBlank()]
    public string $service = '';

}