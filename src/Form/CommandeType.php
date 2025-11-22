<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $builder
            ->add('nom', TextType::class, [
                'mapped' => false,
                'data' => $user ? $user->getName() : '',
                'disabled' => true,
                'label' => 'Nom :'
            ])
            ->add('email', TextType::class, [
                'mapped' => false,
                'data' => $user ? $user->getEmail() : '',
                'disabled' => true,
                'label' => 'Email :'
            ])
            ->add('phone', TextType::class, [
                'mapped' => false,
                'data' => $user ? $user->getPhone() : '',
                'disabled' => true,
                'label' => 'Numéro de téléphone :'
            ])
            ->add('adresseLivraison', TextType::class, [
                'empty_data' => $user ? $user->getAddress() : '',
                'attr' => [
                    'placeholder' => $user ? $user->getAddress() : '',
                ],
                'required' => false,
                'label' => 'Adresse de livraison :'
            ])
            ->add('datePrestation', null, [
                'widget' => 'single_text'
            ])
            ->add('heureLivraison', null, [
                'widget' => 'single_text'
            ])
            ->add('nombrePersonne')
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Commande passée' => 'Commande passée',
                    'Accepté' => 'Accepté',
                    'En préparation' => 'En préparation',
                    'En cours de livraison' => 'En cours de livraison',
                    'Livré' => 'Livré',
                    'En attente du retour de matériel' => 'En attente du retour de matériel',
                    'Terminée' => 'Terminée'
                ]
            ])
            ->add('pretMateriel')
            ->add('restitutionMateriel')
            ->add('prixLivraison', NumberType::class, [
                'data' => 5.99,
                'disabled' => true
            ])
            ->add('prixMenu', NumberType::class, [
                'disabled' => true,
                'label' => 'Prix du menu par personne (Le prix totale varie en fonction du nombre de personne indiquée)'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
            'user' => null
        ]);
    }
}
