<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class UserType extends AbstractType
{
  /**
   * @param FormBuilderInterface $builder
   * @param array $options
   */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('username')
      //->add('password', 'password')
      ->add('firstname')
      ->add('lastname')
      ->add('email')
      ->add('ayso_id')
      ->add('ayso_my', TextType::class, [
        'label' => 'AYSO MY',
      ])
      ->add('badge', TextType::class, [
        'label' => 'Badge',
      ])
      ->add('is_youth', CheckboxType::class, [
        'label' => 'Youth?',
        'required' => false,
      ])
      ->add('role_referee', CheckboxType::class, [
        'label' => 'Referee?',
        'required' => false,
      ])
      ->add('role_referee_admin', CheckboxType::class, [
        'label' => 'Referee Admin?',
        'required' => false,
      ])
      ->add('role_scheduler', CheckboxType::class, [
        'label' => 'Scheduler?',
        'required' => false,
      ])
      ->add('role_assigner', CheckboxType::class, [
        'label' => 'Assigner?',
        'required' => false,
      ])
      ->add('role_superuser', CheckboxType::class, [
        'label' => 'Super User?',
        'required' => false,
      ])
      ->add('enabled', CheckboxType::class, [
        'label' => 'Enable?',
        'required' => false,
      ])
      ->add('phoneHome', TextType::class, [
        'required' => false,
      ])
      ->add('phoneMobile', TextType::class, [
        'required' => false,
      ])
      ->add('mobile_provider', EntityType::class, [
          'class' => 'Scheduler\SchBundle\Entity\MobileProvider',
          'placeholder' => '',
          'required' => false,
          'query_builder' => function (EntityRepository $er) {
            return $er->createQueryBuilder('u')
              ->orderBy('u.name', 'ASC');
          },
      ])
      ->add('region', EntityType::class, [
          'class' => 'Scheduler\SchBundle\Entity\Region',
          'placeholder' => '',
          'query_builder' => function (EntityRepository $er) {
            return $er->createQueryBuilder('u')
              ->orderBy('u.name', 'ASC');
          },
      ])
      ->add('option_change_email', CheckboxType::class, [
        'label' => 'Game change notify email',
        'required' => false,
      ])
      ->add('option_change_text', CheckboxType::class, [
        'label' => 'Game change notify text',
        'required' => false,
      ])
      ->add('option_reminder_email', CheckboxType::class, [
        'label' => 'Game reminder email',
        'required' => false,
      ])
      ->add('option_reminder_text', CheckboxType::class, [
        'label' => 'Game reminder text',
        'required' => false,
      ])
      ->add('option_assignment_email', CheckboxType::class, [
        'label' => 'Game assignment email',
        'required' => false,
      ])
      ->add('option_assignment_text', CheckboxType::class, [
        'label' => 'Game assignment text',
        'required' => false,
      ])
      ->add('option_assigner_email', CheckboxType::class, [
        'label' => 'Game assigner email',
        'required' => false,
      ])
      ->add('option_assigner_text', CheckboxType::class, [
        'label' => 'Game assigner text',
        'required' => false,
      ]);
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults([
      'data_class' => 'Scheduler\SchBundle\Entity\User'
    ]);
  }

  public function getBlockPrefix()
  {
    return 'scheduler_schbundle_usertype';
  }
}
