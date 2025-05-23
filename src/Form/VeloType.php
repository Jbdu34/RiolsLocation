<?php

namespace App\Form;

use App\Entity\Velo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VeloType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('modele', TextType::class, [
                'label' => 'Modèle',
                'attr' => ['placeholder' => 'Ex: VTT Trek X500']
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'VTT' => 'VTT',
                    'Vélo de ville' => 'ville',
                    'Vélo électrique' => 'electrique',
                    'Vélo de route' => 'route'
                ]
            ])
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    'Disponible' => Velo::ETAT_DISPONIBLE,
                    'En location' => Velo::ETAT_EN_LOCATION,
                    'En maintenance' => Velo::ETAT_EN_MAINTENANCE
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Velo::class,
        ]);
    }
} 