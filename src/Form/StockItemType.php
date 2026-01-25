<?php

namespace App\Form;

use App\Entity\Piece;
use App\Entity\StockItem;
use App\Entity\StockLocation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('stockLocation', EntityType::class, [
                'class' => StockLocation::class,
                'choice_label' => function(StockLocation $stockLocation) {
                    return sprintf('%s - %s (%s)', 
                        $stockLocation->getSite()->getNomSite(), 
                        $stockLocation->getNomStock(), 
                        $stockLocation->getType()->value
                    );
                },
                'label' => 'Emplacement de stock',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Emplacement où se trouve cette pièce'
            ])
            ->add('piece', EntityType::class, [
                'class' => Piece::class,
                'choice_label' => function(Piece $piece) {
                    return sprintf('%s - %s', $piece->getReference(), $piece->getDesignation());
                },
                'label' => 'Pièce',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Pièce à stocker'
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité en stock',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'help' => 'Nombre d\'unités actuellement en stock'
            ])
            ->add('seuilAlerte', IntegerType::class, [
                'label' => 'Seuil d\'alerte',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'help' => 'Alerte déclenchée quand la quantité atteint ou passe en dessous de ce seuil (laisser vide pour désactiver)'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StockItem::class,
        ]);
    }
}
