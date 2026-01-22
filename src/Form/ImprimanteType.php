<?php

namespace App\Form;

use App\Entity\Imprimante;
use App\Entity\Modele;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImprimanteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numeroSerie')
            ->add('dateInstallation', null, [
                'widget' => 'single_text'
            ])
            ->add('adresseIp')
            ->add('emplacement')
            ->add('suivieParService')
            ->add('statut')
            ->add('dualScan', null, [
                'label' => 'Scanner Automatique (A)',
                'help' => 'Scanner recto-verso automatique'
            ])
            ->add('notes')
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'id',
            ])
            ->add('modele', EntityType::class, [
                'class' => Modele::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Imprimante::class,
        ]);
    }
}
