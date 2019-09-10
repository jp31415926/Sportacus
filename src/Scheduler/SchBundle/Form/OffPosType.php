<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OffPosType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('name')
      ->add('shortname')
      ->add('diffavail')
      ->add('diffvisable')
      ->add('diffreq');
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'Scheduler\SchBundle\Entity\OffPos'
    ));
  }

  public function getBlockPrefix()
  {
    return 'scheduler_schbundle_offpostype';
  }
}
