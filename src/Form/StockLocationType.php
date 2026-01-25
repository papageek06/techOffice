<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\StockLocation;
use App\Enum\StockLocationType as StockLocationTypeEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockLocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => function(Site $site) {
                    return sprintf('%s - %s', $site->getNomSite(), $site->getClient()->getNom());
                },
                'label' => 'Site',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Site où se trouve cet emplacement de stock'
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Entreprise' => StockLocationTypeEnum::ENTREPRISE,
                    'Client' => StockLocationTypeEnum::CLIENT,
                ],
                'label' => 'Type de stock',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Type d\'emplacement : Entreprise (atelier, dépôt) ou Client (sur site client)'
            ])
            ->add('nomStock', TextType::class, [
                'label' => 'Nom du stock',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Atelier principal, Dépôt secondaire'
                ],
                'help' => 'Nom ou identifiant de cet emplacement de stock'
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Emplacement actif',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'help' => 'Décocher pour désactiver cet emplacement de stock'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StockLocation::class,
        ]);
    }
}
