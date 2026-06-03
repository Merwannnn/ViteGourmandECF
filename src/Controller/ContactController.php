<?php

namespace App\Controller;

use App\DTO\ContactDTO;
use App\Form\ContactType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController{

    #[Route('/contact', name: 'contact')]
    // cette fonction permet uniquement de contacter différents service via un formulaire
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $data = new ContactDTO();
        $form = $this->createForm(ContactType::class, $data);
        $form->handleRequest($request);
        // permet de vérifier si le formulaire est corretement soumis et si il est valide avant d'envoyer le mail
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $mail = (new TemplatedEmail())
                    // permet de passer les données du service qu'on veut contacter du form ContactType au DTO
                    ->to($data->service)
                    // permet de passer les données de l'adresse mail qu'on utilise du form ContactType au DTO
                    ->from($data->email)
                    ->subject('Demande de contact')
                    ->htmlTemplate('emails/contact.html.twig')
                    // permet de passer les données du message qu'on veut envoyer du form ContactType au DTO
                    ->context(['data' => $data]);
                
                    $mailer->send($mail);
                    $this->addFlash('success', 'Le mail a bien été envoyé');
                return $this->redirectToRoute('contact');
            // permet de capturer une erreur(email non envoyé) et d'en informer l'utilisateur qui utilise le formulaire de contact
            } catch (\Exception $e) {
                // permet d'informer l'utilisateur qui il y a eu une erreur
                $this->addFlash('danger', 'Impossible d\'envoyer le mail');
            }
        }
        return $this->render('contact/contact.html.twig', [
            'form' => $form
        ]);
    }
}