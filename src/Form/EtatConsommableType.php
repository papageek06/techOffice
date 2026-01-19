<?php

namespace App\Form;

use App\Entity\EtatConsommable;
use App\Entity\Imprimante;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EtatConsommableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateCapture', null, [
                'widget' => 'single_text'
            ])
            ->add('noirPourcent')
            ->add('cyanPourcent')
            ->add('magentaPourcent')
            ->add('jaunePourcent')
            ->add('bacRecuperation')
            ->add('imprimante', EntityType::class, [
                'class' => Imprimante::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EtatConsommable::class,
        ]);
    }
}
