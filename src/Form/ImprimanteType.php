<?php

namespace App\Form;

use App\Entity\Imprimante;
use App\Entity\Modele;
use App\Entity\Site;
use App\Enum\StatutImprimante;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImprimanteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => function(Site $site) {
                    return sprintf('%s - %s', $site->getNomSite(), $site->getClient()->getNom());
                },
                'label' => 'Site d\'installation',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Site où l\'imprimante est installée'
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
                'help' => 'Modèle de l\'imprimante'
            ])
            ->add('numeroSerie', TextType::class, [
                'label' => 'Numéro de série',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: ABC123456789'
                ],
                'help' => 'Numéro de série unique de l\'imprimante'
            ])
            ->add('dateInstallation', DateType::class, [
                'label' => 'Date d\'installation',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'help' => 'Date à laquelle l\'imprimante a été installée'
            ])
            ->add('adresseIp', TextType::class, [
                'label' => 'Adresse IP',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 192.168.1.100'
                ],
                'help' => 'Adresse IP de l\'imprimante sur le réseau'
            ])
            ->add('emplacement', TextType::class, [
                'label' => 'Emplacement physique',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Bureau 205, RDC couloir'
                ],
                'help' => 'Emplacement physique de l\'imprimante sur le site'
            ])
            ->add('suivieParService', CheckboxType::class, [
                'label' => 'Suivie par le service',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'help' => 'Cocher si cette imprimante est suivie par le service technique'
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Actif' => StatutImprimante::ACTIF,
                    'Prêt' => StatutImprimante::PRET,
                    'Assurance' => StatutImprimante::ASSURANCE,
                    'Hors service' => StatutImprimante::HS,
                    'Déchetterie' => StatutImprimante::DECHETERIE,
                ],
                'label' => 'Statut',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Statut actuel de l\'imprimante'
            ])
            ->add('dualScan', CheckboxType::class, [
                'label' => 'Scanner automatique (A)',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'help' => 'Scanner recto-verso automatique'
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4
                ],
                'help' => 'Notes supplémentaires sur cette imprimante'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Imprimante::class,
        ]);
    }
}
