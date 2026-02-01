<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Contrat;
use App\Enum\StatutContrat;
use App\Enum\TypeContrat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContratType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'nom',
                'label' => 'Client',
                'placeholder' => 'Sélectionner un client',
            ])
            ->add('reference', TextType::class, [
                'label' => 'Référence du contrat',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: CONT-0001-2026',
                ],
            ])
            ->add('typeContrat', ChoiceType::class, [
                'label' => 'Type de contrat',
                'choices' => [
                    'Maintenance' => TypeContrat::MAINTENANCE,
                    'Location' => TypeContrat::LOCATION,
                    'Vente' => TypeContrat::VENTE,
                    'Prêt' => TypeContrat::PRET,
                ],
                'choice_label' => fn (?TypeContrat $type) => $type?->value,
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Brouillon' => StatutContrat::BROUILLON,
                    'Actif' => StatutContrat::ACTIF,
                    'Terminé' => StatutContrat::TERMINE,
                ],
                'choice_label' => fn (?StatutContrat $statut) => $statut?->value,
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Informations complémentaires sur le contrat',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contrat::class,
        ]);
    }
}

