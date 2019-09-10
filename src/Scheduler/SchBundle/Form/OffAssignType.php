<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OffAssignType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('game')
      ->add('ref')
      ->add('ar1')
      ->add('ar2');
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'Scheduler\SchBundle\Entity\OffAssign'
    ));
  }

  public function getBlockPrefix()
  {
    return 'scheduler_schbundle_offassigntype';
  }
}
