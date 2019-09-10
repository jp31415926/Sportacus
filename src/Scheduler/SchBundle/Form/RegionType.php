<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegionType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('name')
      ->add('longname')
      ->add('pocname', TextType::class, array(
        'label' => 'POC Name',
        'required' => false,
      ))
      ->add('pocemail', TextType::class, array(
        'label' => 'POC Email',
        'required' => false,
      ))
      ->add('refadminname', TextType::class, array(
        'label' => 'Ref Admin Name',
        'required' => false,
      ))
      ->add('refadminemail', TextType::class, array(
        'label' => 'Ref Admin Email',
        'required' => false,
      ));
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'Scheduler\SchBundle\Entity\Region'
    ));
  }

  public function getBlockPrefix()
  {
    return 'scheduler_schbundle_regiontype';
  }
}
