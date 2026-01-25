<?php

namespace App\Form;

use App\Entity\Modele;
use App\Entity\Piece;
use App\Entity\PieceModele;
use App\Enum\PieceRoleModele as PieceRoleModeleEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PieceModeleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('piece', EntityType::class, [
                'class' => Piece::class,
                'choice_label' => function(Piece $piece) {
                    return sprintf('%s - %s', $piece->getReference(), $piece->getDesignation());
                },
                'label' => 'Pièce',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Pièce compatible avec le modèle sélectionné'
            ])
            ->add('modele', EntityType::class, [
                'class' => Modele::class,
                'choice_label' => function(Modele $modele) {
                    return sprintf('%s %s', $modele->getFabricant()->getNomFabricant(), $modele->getReferenceModele());
                },
                'label' => 'Modèle d\'imprimante',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Modèle d\'imprimante compatible avec cette pièce'
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Toner Noir (K)' => PieceRoleModeleEnum::TONER_K,
                    'Toner Cyan (C)' => PieceRoleModeleEnum::TONER_C,
                    'Toner Magenta (M)' => PieceRoleModeleEnum::TONER_M,
                    'Toner Jaune (Y)' => PieceRoleModeleEnum::TONER_Y,
                    'Bac de récupération' => PieceRoleModeleEnum::BAC_RECUP,
                    'Tambour' => PieceRoleModeleEnum::DRUM,
                    'Unité de fusion' => PieceRoleModeleEnum::FUSER,
                    'Autre' => PieceRoleModeleEnum::AUTRE,
                ],
                'label' => 'Rôle pour ce modèle',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Rôle de cette pièce pour ce modèle (ex: Toner Noir, Toner Cyan, etc.)'
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3
                ],
                'help' => 'Notes ou informations complémentaires sur cette compatibilité'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PieceModele::class,
        ]);
    }
}
