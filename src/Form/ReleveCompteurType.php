<?php

namespace App\Form;

use App\Entity\Imprimante;
use App\Entity\ReleveCompteur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReleveCompteurType extends AbstractType
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
                'help' => 'Imprimante concernée par ce relevé'
            ])
            ->add('dateReleve', DateType::class, [
                'label' => 'Date du relevé',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ],
                'help' => 'Date à laquelle le compteur a été relevé'
            ])
            ->add('compteurNoir', IntegerType::class, [
                'label' => 'Compteur noir',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'help' => 'Nombre d\'impressions en noir et blanc'
            ])
            ->add('compteurCouleur', IntegerType::class, [
                'label' => 'Compteur couleur',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'help' => 'Nombre d\'impressions en couleur'
            ])
            ->add('source', ChoiceType::class, [
                'choices' => [
                    'Manuel' => 'manuel',
                    'SNMP' => 'snmp',
                    'Scan automatique' => 'scan',
                ],
                'label' => 'Source du relevé',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Méthode utilisée pour obtenir ce relevé'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReleveCompteur::class,
        ]);
    }
}
