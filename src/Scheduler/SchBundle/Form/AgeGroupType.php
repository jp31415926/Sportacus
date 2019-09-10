<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgeGroupType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('name')
      ->add('difficulty')
      ->add('region')
      //->add('points_ref1', 'integer', array(
      //    'label' => 'Points Multiplier',
      //))
      ->add('points_ref1')
      ->add('points_youth_ref1')
      ->add('points_ref2')
      ->add('points_youth_ref2')
      ->add('points_ref3')
      ->add('points_youth_ref3')
      ->add('points_team_goal');
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'Scheduler\SchBundle\Entity\AgeGroup'
    ));
  }

  public function getBlockPrefix()
  {
    return 'scheduler_schbundle_agegrouptype';
  }
}
