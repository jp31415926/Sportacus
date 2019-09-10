<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('delivery_date', DateTimeType::class, array(
        'widget' => 'single_text',
      ))
      ->add('date', DateTimeType::class, array(
        'widget' => 'single_text',
      ))
      ->add('sent_to')
      ->add('sent_from')
      ->add('subject')
      ->add('message')
      ->add('media_type')
      ->add('type')
      ->add('data');
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'Scheduler\SchBundle\Entity\Message'
    ));
  }

  public function getBlockPrefix()
  {
    return 'scheduler_schbundle_messagetype';
  }
}
