<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
//use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('name')
      ->add('long_name')
      ->add('start_date', DateType::class, array(
        'widget' => 'single_text',
      ))
      ->add('end_date', DateType::class, array(
        'widget' => 'single_text',
      ))
      ->add('archived', CheckboxType::class, array(
        'label' => 'Archived?',
        'required' => false,
      ))
      ->add('use_team_refpnt_rules', CheckboxType::class, array(
        'label' => 'Use Team\'s Region Rules?',
        'required' => false,
      ))
      ->add('show_referee_region', CheckboxType::class, array(
        'label' => 'Show Referee Region?',
        'required' => false,
      ))
      ->add('sportstr', TextType::class, array(
        'label' => 'Sport String (lowercase)',
        'required' => false,
      ))
      ->add('offpositions', CollectionType::class, array(
        'label' => 'Official Positions',
        //'type' => new OffPosType(),
        'entry_type' => OffPosType::class,
        'allow_add' => true,
        'allow_delete' => true,
        'by_reference' => false,
        'prototype' => true,
        //'prototype_name' => 'pos__name__',
        'entry_options' => array(// options on the rendered OffPos
        ),
      ));
  }

  public function configureOptions(OptionsResolver  $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'Scheduler\SchBundle\Entity\Project'
    ));
  }

  public function getBlockPrefix()
  {
    return 'scheduler_schbundle_projecttype';
  }
}
