<?php

namespace App\Form;

use App\Entity\EtatConsommable;
use App\Entity\Imprimante;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EtatConsommableType extends AbstractType
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
                'help' => 'Imprimante concernée par cet état'
            ])
            ->add('dateCapture', DateTimeType::class, [
                'label' => 'Date de capture',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ],
                'help' => 'Date et heure de la capture de l\'état'
            ])
            ->add('noirPourcent', IntegerType::class, [
                'label' => 'Niveau noir (%)',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 100
                ],
                'help' => 'Niveau du toner noir en pourcentage (0-100)'
            ])
            ->add('cyanPourcent', IntegerType::class, [
                'label' => 'Niveau cyan (%)',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 100
                ],
                'help' => 'Niveau du toner cyan en pourcentage (0-100)'
            ])
            ->add('magentaPourcent', IntegerType::class, [
                'label' => 'Niveau magenta (%)',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 100
                ],
                'help' => 'Niveau du toner magenta en pourcentage (0-100)'
            ])
            ->add('jaunePourcent', IntegerType::class, [
                'label' => 'Niveau jaune (%)',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 100
                ],
                'help' => 'Niveau du toner jaune en pourcentage (0-100)'
            ])
            ->add('bacRecuperation', TextType::class, [
                'label' => 'État du bac de récupération',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 50%, Plein, etc.'
                ],
                'help' => 'État du bac de récupération (pourcentage ou description)'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EtatConsommable::class,
        ]);
    }
}
