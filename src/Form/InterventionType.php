<?php

namespace App\Form;

use App\Entity\Imprimante;
use App\Entity\Intervention;
use App\Entity\User;
use App\Enum\StatutIntervention;
use App\Enum\TypeIntervention;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterventionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $fromSite = $options['from_site'] ?? false;
        $site = $options['site'] ?? null;

        $imprimanteOptions = [
            'class' => Imprimante::class,
            'choice_label' => function (Imprimante $imprimante) {
                return sprintf('%s %s - %s (%s)',
                    $imprimante->getModele()->getFabricant()->getNomFabricant(),
                    $imprimante->getModele()->getReferenceModele(),
                    $imprimante->getSite()->getNomSite(),
                    $imprimante->getNumeroSerie()
                );
            },
            'label' => 'Imprimante',
            'attr' => ['class' => 'form-select'],
            'help' => 'Imprimante concernée par cette intervention',
        ];
        if ($site) {
            $imprimanteOptions['choices'] = $site->getImprimantes();
        }
        $builder->add('imprimante', EntityType::class, $imprimanteOptions);

        $builder
            ->add('typeIntervention', ChoiceType::class, [
                'choices' => [
                    'Sur site' => TypeIntervention::SUR_SITE,
                    'À distance' => TypeIntervention::DISTANCE,
                    'En atelier' => TypeIntervention::ATELIER,
                    'Livraison toner' => TypeIntervention::LIVRAISON_TONER,
                ],
                'choice_label' => fn (TypeIntervention $type) => match ($type) {
                    TypeIntervention::SUR_SITE => 'Sur site',
                    TypeIntervention::DISTANCE => 'À distance',
                    TypeIntervention::ATELIER => 'En atelier',
                    TypeIntervention::LIVRAISON_TONER => 'Livraison toner',
                },
                'label' => 'Type d\'intervention',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Type d\'intervention effectuée'
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Ouverte' => StatutIntervention::OUVERTE,
                    'En cours' => StatutIntervention::EN_COURS,
                    'Terminée' => StatutIntervention::TERMINEE,
                    'Annulée' => StatutIntervention::ANNULEE,
                ],
                'choice_label' => fn (StatutIntervention $s) => match ($s) {
                    StatutIntervention::OUVERTE => 'Ouverte',
                    StatutIntervention::EN_COURS => 'En cours',
                    StatutIntervention::TERMINEE => 'Terminée',
                    StatutIntervention::ANNULEE => 'Annulée',
                },
                'label' => 'Statut',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Statut actuel de l\'intervention'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'empty_data' => '',
                'attr' => ['class' => 'form-control', 'rows' => 4],
                'help' => 'Description détaillée de l\'intervention',
            ])
            ->add('tempsFacturableMinutes', IntegerType::class, [
                'label' => 'Temps facturable (minutes)',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'help' => 'Temps facturable en minutes'
            ])
            ->add('tempsReelMinutes', IntegerType::class, [
                'label' => 'Temps réel (minutes)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'help' => 'Temps réel passé sur l\'intervention (optionnel)'
            ])
            ->add('facturable', CheckboxType::class, [
                'label' => 'Facturable',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('lignes', CollectionType::class, [
                'entry_type' => InterventionLigneType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Pièces (livrées / installées)',
                'help' => 'Toners et bacs récup. : livrés au stock du site. Autres pièces : installées, débitées du stock entreprise à la clôture.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
            'site' => null,
            'from_site' => false,
        ]);
    }
}
