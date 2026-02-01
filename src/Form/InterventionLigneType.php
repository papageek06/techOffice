<?php

namespace App\Form;

use App\Entity\InterventionLigne;
use App\Entity\Piece;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterventionLigneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('piece', EntityType::class, [
                'class' => Piece::class,
                'choice_label' => function (Piece $piece) {
                    return sprintf('%s - %s', $piece->getReference(), $piece->getDesignation());
                },
                'label' => 'Pièce',
                'attr' => ['class' => 'form-select form-select-sm'],
                'placeholder' => 'Choisir une pièce',
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Qté',
                'attr' => ['class' => 'form-control form-control-sm', 'min' => 1, 'value' => 1],
                'empty_data' => 1,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InterventionLigne::class,
        ]);
        $resolver->setDefined(['pieces']);
        $resolver->setDefault('pieces', []);
    }
}
