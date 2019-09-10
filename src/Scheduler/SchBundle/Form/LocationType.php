<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('name')
      ->add('long_name')
      ->add('street1')
      ->add('street2')
      ->add('city')
      ->add('state')
      ->add('zip')
      ->add('latitude')
      ->add('longitude')
      ->add('poc_name')
      ->add('poc_phone1')
      ->add('poc_phone2')
      ->add('poc_email1')
      ->add('poc_email2')
      ->add('url');
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'Scheduler\SchBundle\Entity\Location'
    ));
  }

  public function getBlockPrefix()
  {
    return 'scheduler_schbundle_locationtype';
  }
}
