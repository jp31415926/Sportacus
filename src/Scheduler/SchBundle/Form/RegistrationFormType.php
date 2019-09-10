<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Doctrine\ORM\EntityRepository;

class RegistrationFormType extends BaseType
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
      ])
      ->add('phone_mobile', TextType::class, [
        'label' => 'Mobile Phone',
      ])
      ->add('mobile_provider')
      ->add('ayso_id', TextType::class, [
        'label' => 'AYSO ID',
      ])
      //->add('region');
      ->add('region', EntityType::class, [
          'class' => 'Scheduler\SchBundle\Entity\Region',
          'placeholder' => '',
          //'query_builder' => function (EntityRepository $er) {
          //  return $er->createQueryBuilder('u')
          //    ->orderBy('u.name', 'ASC');
          //},
      ]);
  }

  public function getParent()
  {
    return 'FOS\UserBundle\Form\Type\RegistrationFormType';
  }

  public function getBlockPrefix()
  {
    return 'user_registration';
  }

  // For Symfony 2.x
  public function getName()
  {
    return $this->getBlockPrefix();
  }
}
