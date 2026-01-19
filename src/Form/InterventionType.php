<?php

namespace App\Form;

use App\Entity\Imprimante;
use App\Entity\Intervention;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterventionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateCreation', null, [
                'widget' => 'single_text'
            ])
            ->add('dateIntervention', null, [
                'widget' => 'single_text'
            ])
            ->add('typeIntervention')
            ->add('statut')
            ->add('description')
            ->add('tempsFacturableMinutes')
            ->add('tempsReelMinutes')
            ->add('facturable')
            ->add('imprimante', EntityType::class, [
                'class' => Imprimante::class,
                'choice_label' => 'id',
            ])
            ->add('utilisateur', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
        ]);
    }
}
