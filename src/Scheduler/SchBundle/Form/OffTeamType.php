<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OffTeamType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('name')
      ->add('positions', CollectionType::class, array(
        'type' => new OffPosType(),
        'allow_add' => true,
        'allow_delete' => true,
        'by_reference' => false,
        'prototype' => true,
        //'prototype_name' => 'pos__name__',
        'options' => array(// options on the rendered OffPos
        ),
      ));
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'Scheduler\SchBundle\Entity\OffTeam'
    ));
  }

  public function getBlockPrefix()
  {
    return 'scheduler_schbundle_offteamtype';
  }
}
