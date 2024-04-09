<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UploadUserCsvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('csvFile', FileType:: class, [
                'label' => 'CSV File',
                'mapped' => false,
                'required' => true,
                'attr' => ['accept' => '.csv'],
                'constraints' => [
                        new File([
                            'mimeTypes' => ['text/csv', 'text/plain'],
                            'mimeTypesMessage' => 'Please upload a valid CSV file (text/csv or text/plain)',
        ]),
    ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
