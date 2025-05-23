<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Location;
use App\Entity\Velo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDebut', DateTimeType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('dateFin', DateTimeType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => function(Client $client) {
                    return $client->getNom() . ' ' . $client->getPrenom();
                },
                'placeholder' => 'Choisir un client',
                'required' => true,
            ])
            ->add('velo', EntityType::class, [
                'class' => Velo::class,
                'choice_label' => function(Velo $velo) {
                    return $velo->getModele() . ' (' . $velo->getType() . ')';
                },
                'placeholder' => 'Choisir un vélo',
                'required' => true,
                'query_builder' => function ($er) {
                    return $er->createQueryBuilder('v')
                        ->where('v.etat = :etat')
                        ->setParameter('etat', Velo::ETAT_DISPONIBLE);
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Location::class,
        ]);
    }
} 