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
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterventionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imprimante', EntityType::class, [
                'class' => Imprimante::class,
                'choice_label' => function(Imprimante $imprimante) {
                    return sprintf('%s %s - %s (%s)', 
                        $imprimante->getModele()->getFabricant()->getNomFabricant(),
                        $imprimante->getModele()->getReferenceModele(),
                        $imprimante->getSite()->getNomSite(),
                        $imprimante->getNumeroSerie()
                    );
                },
                'label' => 'Imprimante',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Imprimante concernée par cette intervention'
            ])
            ->add('dateCreation', DateTimeType::class, [
                'label' => 'Date de création',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ],
                'help' => 'Date et heure de création de l\'intervention'
            ])
            ->add('dateIntervention', DateTimeType::class, [
                'label' => 'Date d\'intervention',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'help' => 'Date et heure prévues ou effectives de l\'intervention'
            ])
            ->add('typeIntervention', ChoiceType::class, [
                'choices' => [
                    'Sur site' => TypeIntervention::SUR_SITE,
                    'À distance' => TypeIntervention::DISTANCE,
                    'En atelier' => TypeIntervention::ATELIER,
                ],
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
                'label' => 'Statut',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Statut actuel de l\'intervention'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5
                ],
                'help' => 'Description détaillée de l\'intervention'
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
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'help' => 'Cocher si cette intervention est facturable au client'
            ])
            ->add('utilisateur', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $user) {
                    return sprintf('%s (%s)', $user->getNom() ?? $user->getEmail(), $user->getEmail());
                },
                'label' => 'Technicien',
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Technicien ayant effectué l\'intervention'
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
