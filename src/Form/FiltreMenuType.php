<?php

namespace App\Form;

use App\Entity\Theme;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreMenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prixMax', NumberType::class, [
                'label' => 'Prix maximum',
                'required' => false
            ])
            ->add('prixMin', NumberType::class, [
                'label' => 'Prix minimum',
                'required' => false
            ])
            ->add('theme', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'themeTitle',
                'required' => false
            ])
            ->add('regime', TextType::class, [
                'label' => 'Régime',
                'required' => false
            ])
            ->add('personneMin', NumberType::class, [
                'label' => 'Nombre de personne minimum',
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
