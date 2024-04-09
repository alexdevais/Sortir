<?php

namespace App\Form;

use App\Entity\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('firstName', TextType::class)
            ->add('phoneNumber', TextType::class, [
                'required' => false
            ])
            ->add('email', EmailType::class)

            ->add('photo', FileType::class, [
                'label' => 'Photo',
                'mapped' => false,
                'required' => false,


                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid photo',
                    ])
                ],
            ])
            ->add('refresh', ButtonType::class, [
                'label' => 'Clear file',
                'attr' => [
                    'onclick' => 'event.preventDefault(); this.closest(\'form\').reset();'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Submit'
            ]);


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
