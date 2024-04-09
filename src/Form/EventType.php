<?php

namespace App\Form;

use App\Entity\Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('firstAirDate', null, [
                'widget' => 'single_text',
            ])
            ->add('duration')
            ->add('dateLimitationInscription', null, [
                'widget' => 'single_text',
            ])
            ->add('nbInscriptionMax')
            ->add('description')
            ->add('state',ChoiceType::class,[
                'placeholder' => '-- Choose an option --',
                'choices'=>[
                    'Created' => 'CREATED',
                    'Open'=> 'OPEN',
                    'Current activity' => 'CURRENT',
                    'Past' => 'PAST',
                    'Cancel' => 'CANCEL',
                ]
            ])
            ->add('location', LocationType::class, [])
            ->add('submit', SubmitType::class,[
                'attr' => [
                    'class' => 'btn btn-light',
            ]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
