<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\Menu;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
