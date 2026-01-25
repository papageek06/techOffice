<?php

namespace App\Form;

use App\Entity\Piece;
use App\Enum\PieceType as PieceTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PieceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', TextType::class, [
                'label' => 'Référence',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: HP305A, CE285A'
                ],
                'help' => 'Référence unique de la pièce (code fabricant)'
            ])
            ->add('designation', TextType::class, [
                'label' => 'Désignation',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Toner Noir HP LaserJet'
                ],
                'help' => 'Description ou nom commercial de la pièce'
            ])
            ->add('typePiece', ChoiceType::class, [
                'choices' => [
                    'Toner' => PieceTypeEnum::TONER,
                    'Bac récupération' => PieceTypeEnum::BAC_RECUP,
                    'Tambour' => PieceTypeEnum::DRUM,
                    'Unité de fusion' => PieceTypeEnum::FUSER,
                    'Kit de maintenance' => PieceTypeEnum::MAINTENANCE_KIT,
                    'Autre' => PieceTypeEnum::AUTRE,
                ],
                'label' => 'Type de pièce',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Catégorie de la pièce consommable'
            ])
            ->add('couleur', ChoiceType::class, [
                'choices' => [
                    'Noir (K)' => 'K',
                    'Cyan (C)' => 'C',
                    'Magenta (M)' => 'M',
                    'Jaune (Y)' => 'Y',
                ],
                'label' => 'Couleur (pour toners)',
                'required' => false,
                'placeholder' => 'Non applicable',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Couleur du toner (uniquement pour les toners)'
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Pièce active',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'help' => 'Décocher pour désactiver cette pièce dans le catalogue'
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3
                ],
                'help' => 'Notes ou informations complémentaires sur cette pièce'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Piece::class,
        ]);
    }
}
