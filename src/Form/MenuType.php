<?php

namespace App\Form;

use App\Entity\Menu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du menu'
            ])
            ->add('nbPersonneMinimum', IntegerType::class, [
                'label' => 'Nombre de personne minimum'
            ])
            ->add('prixPersonne', NumberType::class, [
                'label' => 'Prix par personne',
                'scale' => 2
            ])
            ->add('regime', TextType::class, [
                'label' => 'Régime'
            ])
            ->add('description', TextType::class, [
                'label' => 'Description'
            ])
            ->add('quantiteRestante', IntegerType::class, [
                'label' => 'Quantité restante'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
        ]);
    }
}
