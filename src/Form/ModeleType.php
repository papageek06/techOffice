<?php

namespace App\Form;

use App\Entity\Fabricant;
use App\Entity\Modele;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModeleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fabricant', EntityType::class, [
                'class' => Fabricant::class,
                'choice_label' => function(Fabricant $fabricant) {
                    return $fabricant->getNomFabricant();
                },
                'label' => 'Fabricant',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Fabricant de ce modèle d\'imprimante'
            ])
            ->add('referenceModele', TextType::class, [
                'label' => 'Référence du modèle',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: LaserJet Pro M404dn'
                ],
                'help' => 'Référence ou nom du modèle'
            ])
            ->add('couleur', CheckboxType::class, [
                'label' => 'Imprimante couleur',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'help' => 'Cocher si ce modèle imprime en couleur'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Modele::class,
        ]);
    }
}
