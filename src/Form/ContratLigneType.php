<?php

namespace App\Form;

use App\Entity\Contrat;
use App\Entity\ContratLigne;
use App\Entity\Site;
use App\Enum\Periodicite;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContratLigneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contrat', EntityType::class, [
                'class' => Contrat::class,
                'choice_label' => 'reference',
                'label' => 'Contrat',
                'placeholder' => 'Sélectionner un contrat',
                'required' => true,
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => function(Site $site) {
                    return sprintf('%s - %s', $site->getNomSite(), $site->getClient()->getNom());
                },
                'label' => 'Site',
                'placeholder' => 'Sélectionner un site',
                'required' => true,
            ])
            ->add('libelle', TextType::class, [
                'label' => 'Libellé',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Maintenance site principal',
                ],
            ])
            ->add('periodicite', ChoiceType::class, [
                'label' => 'Périodicité',
                'choices' => [
                    'Mensuel' => Periodicite::MENSUEL,
                    'Trimestriel' => Periodicite::TRIMESTRIEL,
                    'Semestriel' => Periodicite::SEMESTRIEL,
                    'Annuel' => Periodicite::ANNUEL,
                ],
                'choice_label' => fn (?Periodicite $periodicite) => $periodicite?->value,
                'required' => true,
            ])
            ->add('prochaineFacturation', DateType::class, [
                'label' => 'Prochaine facturation',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('prixFixe', NumberType::class, [
                'label' => 'Prix fixe (€)',
                'required' => false,
                'scale' => 2,
                'attr' => [
                    'placeholder' => '0.00',
                    'step' => '0.01',
                ],
                'help' => 'Prix fixe (abonnement) en euros',
            ])
            ->add('prixPageNoir', NumberType::class, [
                'label' => 'Prix par page noir (€)',
                'required' => false,
                'scale' => 4,
                'attr' => [
                    'placeholder' => '0.0000',
                    'step' => '0.0001',
                ],
                'help' => 'Prix par page noire en euros',
            ])
            ->add('prixPageCouleur', NumberType::class, [
                'label' => 'Prix par page couleur (€)',
                'required' => false,
                'scale' => 4,
                'attr' => [
                    'placeholder' => '0.0000',
                    'step' => '0.0001',
                ],
                'help' => 'Prix par page couleur en euros',
            ])
            ->add('pagesInclusesNoir', IntegerType::class, [
                'label' => 'Pages incluses (noir)',
                'required' => false,
                'attr' => [
                    'min' => 0,
                    'placeholder' => '0',
                ],
                'help' => 'Nombre de pages noires incluses dans le forfait',
            ])
            ->add('pagesInclusesCouleur', IntegerType::class, [
                'label' => 'Pages incluses (couleur)',
                'required' => false,
                'attr' => [
                    'min' => 0,
                    'placeholder' => '0',
                ],
                'help' => 'Nombre de pages couleur incluses dans le forfait',
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Ligne active',
                'required' => false,
                'help' => 'Cocher si cette ligne de contrat est active',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContratLigne::class,
        ]);
    }
}
