<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Security;

class ProfileFormType extends BaseType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    parent::buildForm($builder, $options);
    $builder
      ->add('firstname', TextType::class, [
        'label' => 'First Name',
      ])
      ->add('lastname', TextType::class, [
        'label' => 'Last Name',
      ])
      ->add('phone_home', TextType::class, [
        'label' => 'Home Phone',
        'required' => false,
      ])
      ->add('phone_mobile', TextType::class, [
        'label' => 'Mobile Phone',
        'required' => false,
      ])
      ->add('mobile_provider', EntityType::class, [
          'class' => 'Scheduler\SchBundle\Entity\MobileProvider',
          'placeholder' => '',
          'required' => false,
          'query_builder' => function (EntityRepository $er) {
            return $er->createQueryBuilder('u')
              ->orderBy('u.name', 'ASC');
          }]
      )
      ->add('option_change_email', CheckboxType::class, [
        'label' => 'Game change notify email',
        'required' => false,
      ])
      ->add('option_change_text', CheckboxType::class, [
        'label' => 'Game change notify text',
        'required' => false,
      ])
      ->add('option_reminder_email', CheckboxType::class, [
        'label' => 'Game change email',
        'required' => false,
      ])
      ->add('option_reminder_text', CheckboxType::class, [
        'label' => 'Game change text',
        'required' => false,
      ])
      ->add('option_assignment_email', CheckboxType::class, [
        'label' => 'My game assignment email',
        'required' => false,
      ])
      ->add('option_assignment_text', CheckboxType::class, [
        'label' => 'My game assignment text',
        'required' => false,
      ])
      // TODO: make these only show up for assigners
      ->add('option_assigner_email', CheckboxType::class, [
        'label' => 'All official assignment emails',
        'required' => false,
      ])
      ->add('option_assigner_text', CheckboxType::class, [
        'label' => 'All official assignment texts',
        'required' => false,
      ]);
   /*->add('ayso_id', 'text', [
            'label' => 'AYSO ID',
    'read_only' => true,
            'required' => false,
        ))
        ->add('region', 'entity', [
            'class' => 'Scheduler\SchBundle\Entity\Region',
            //'empty_value' => '',
            'required' => false,
    'read_only' => true,
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('u')
                      ->orderBy('u.name', 'ASC');
            },
    ))*/
  }

  public function getParent()
  {
    return 'FOS\UserBundle\Form\Type\ProfileFormType';
  }

  public function getBlockPrefix()
  {
    return 'user_profile';
  }

  // For Symfony 2.x
  public function getName()
  {
    return $this->getBlockPrefix();
  }
}
