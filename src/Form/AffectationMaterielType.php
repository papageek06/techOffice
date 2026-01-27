<?php

namespace App\Form;

use App\Entity\ContratLigne;
use App\Entity\Imprimante;
use App\Enum\TypeAffectation as TypeAffectationEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AffectationMaterielType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contratLigne', EntityType::class, [
                'class' => ContratLigne::class,
                'choice_label' => function(ContratLigne $ligne) {
                    return sprintf('%s - %s (%s)', 
                        $ligne->getLibelle(),
                        $ligne->getSite()->getNomSite(),
                        $ligne->getContrat()->getReference()
                    );
                },
                'label' => 'Ligne de contrat',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Ligne de contrat concernée'
            ])
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
                'help' => 'Imprimante à affecter à cette ligne de contrat'
            ])
            ->add('dateDebut', DateTimeType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ],
                'help' => 'Date et heure de début de l\'affectation'
            ])
            ->add('typeAffectation', ChoiceType::class, [
                'choices' => [
                    'Principale' => TypeAffectationEnum::PRINCIPALE,
                    'Remplacement temporaire' => TypeAffectationEnum::REMPLACEMENT_TEMP,
                    'Remplacement définitif' => TypeAffectationEnum::REMPLACEMENT_DEF,
                    'Prêt' => TypeAffectationEnum::PRET,
                ],
                'label' => 'Type d\'affectation',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Type d\'affectation de cette imprimante'
            ])
            ->add('reason', TextareaType::class, [
                'label' => 'Raison',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3
                ],
                'help' => 'Raison du changement ou de l\'affectation (optionnel)'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\AffectationMateriel::class,
        ]);
    }
}
