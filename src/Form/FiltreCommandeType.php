<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreCommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom du client',
                'attr' => [
                    'placeholder' => 'Veuiller indiquer le nom du client',
                ],
                'required' => false
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Commande passée' => 'Commande passée',
                    'Accepté' => 'Accepté',
                    'En préparation' => 'En préparation',
                    'En cours de livraison' => 'En cours de livraison',
                    'Livré' => 'Livré',
                    'En attente du retour de matériel' => 'En attente du retour de matériel',
                    'Terminée' => 'Terminée'
                ],
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'method' => 'GET'
        ]);
    }
}
