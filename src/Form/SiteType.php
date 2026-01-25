<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomSite', TextType::class, [
                'label' => 'Nom du site',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Siège social, Agence Paris'
                ],
                'help' => 'Nom ou identifiant du site d\'installation'
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => function(Client $client) {
                    return $client->getNom();
                },
                'label' => 'Client',
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Client propriétaire de ce site'
            ])
            ->add('principal', CheckboxType::class, [
                'label' => 'Site principal',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'help' => 'Cocher si c\'est le site principal du client'
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Site actif',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'help' => 'Décocher pour désactiver ce site'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Site::class,
        ]);
    }
}
