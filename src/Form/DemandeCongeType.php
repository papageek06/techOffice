<?php

namespace App\Form;

use App\Entity\DemandeConge;
use App\Entity\User;
use App\Enum\StatutDemandeConge;
use App\Enum\TypeConge;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeCongeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('utilisateur', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $user) {
                    return sprintf('%s (%s)', $user->getNom() ?? $user->getEmail(), $user->getEmail());
                },
                'label' => 'Utilisateur',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Utilisateur demandant le congé'
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ],
                'help' => 'Date de début du congé'
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ],
                'help' => 'Date de fin du congé'
            ])
            ->add('typeConge', ChoiceType::class, [
                'choices' => [
                    'Congé payé' => TypeConge::PAYE,
                    'Sans solde' => TypeConge::SANS_SOLDE,
                    'Maladie' => TypeConge::MALADIE,
                ],
                'label' => 'Type de congé',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Type de congé demandé'
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Demandée' => StatutDemandeConge::DEMANDEE,
                    'Acceptée' => StatutDemandeConge::ACCEPTEE,
                    'Refusée' => StatutDemandeConge::REFUSEE,
                    'Annulée' => StatutDemandeConge::ANNULEE,
                ],
                'label' => 'Statut',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Statut actuel de la demande'
            ])
            ->add('dateDemande', DateType::class, [
                'label' => 'Date de la demande',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ],
                'help' => 'Date à laquelle la demande a été effectuée'
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4
                ],
                'help' => 'Commentaires ou notes sur cette demande'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DemandeConge::class,
        ]);
    }
}
