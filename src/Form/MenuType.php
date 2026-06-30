<?php

namespace App\Form;

use App\Entity\Menu;
use App\Entity\Plat;
use App\Entity\Theme;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
            ->add('theme', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'theme_title',
                'placeholder' => 'Choisisser un thême',
                'required' => false,
                'label' => 'Thême du menu'
            ])
            ->add('plat', EntityType::class, [
                'class' => Plat::class,
                'choice_label' => 'plat_title',
                'label' => 'Entrée, Plat principale et Dessert',
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'form-select',
                    'size' => 10
                ]
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
            ->add('description', TextareaType::class, [
                'label' => 'Description'
            ])
            ->add('conditions', TextType::class, [
                'label' => 'Conditions'
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
