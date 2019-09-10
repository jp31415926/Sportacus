<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LogType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('dt')
      ->add('info')
      ->add('description')
      ->add('user');
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'Scheduler\SchBundle\Entity\Log'
    ));
  }

  public function getBlockPrefix()
  {
    return 'scheduler_schbundle_logtype';
  }
}
