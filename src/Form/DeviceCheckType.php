<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Formulaire pour la validation du code OTP et le choix de confiance de l'appareil
 */
class DeviceCheckType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('otp', TextType::class, [
                'label' => 'Code de vérification',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '123456',
                    'maxlength' => 6,
                    'autocomplete' => 'off',
                    'inputmode' => 'numeric',
                    'pattern' => '[0-9]{6}'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir le code de vérification',
                    ]),
                    new Length([
                        'min' => 6,
                        'max' => 6,
                        'exactMessage' => 'Le code doit contenir exactement 6 chiffres',
                    ]),
                    new Regex([
                        'pattern' => '/^[0-9]{6}$/',
                        'message' => 'Le code doit contenir uniquement des chiffres',
                    ]),
                ],
                'help' => 'Entrez le code à 6 chiffres reçu par SMS',
            ])
            ->add('trustDuration', ChoiceType::class, [
                'label' => 'Durée de confiance',
                'choices' => [
                    '3 heures' => '3_hours',
                    'Définitivement' => 'permanent',
                ],
                'expanded' => true,
                'multiple' => false,
                'data' => '3_hours', // Valeur par défaut
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'help' => 'Choisissez la durée pendant laquelle cet appareil sera reconnu',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Pas de data_class car on traite les données manuellement
        ]);
    }
}
