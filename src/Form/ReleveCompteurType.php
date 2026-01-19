<?php

namespace App\Form;

use App\Entity\Imprimante;
use App\Entity\ReleveCompteur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReleveCompteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateReleve', null, [
                'widget' => 'single_text'
            ])
            ->add('compteurNoir')
            ->add('compteurCouleur')
            ->add('source')
            ->add('imprimante', EntityType::class, [
                'class' => Imprimante::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReleveCompteur::class,
        ]);
    }
}
