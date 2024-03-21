<?php

namespace App\Form;

use App\Entity\Forum;
use App\Entity\Thread;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ScheduledThreadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titleThread')
            ->add('scheduledPublishTime', TextType::class, [
                'label' => 'Scheduled Publish Time (YYYY-MM-DD HH:MM:SS)',
            ])
            ->add('creationDateThread', TextType::class, [
                'required' => true,
                'attr' => ['placeholder' => 'Enter creation date (YYYY-MM-DD HH:MM:SS)'],
            ])

            ->add('user', EntityType::class, [
                'class' => 'App\Entity\User',
                'choice_label' => 'username',
                'placeholder' => 'Select a user',
                'required' => true,
            ])

            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Thread::class,
        ]);
    }
}
