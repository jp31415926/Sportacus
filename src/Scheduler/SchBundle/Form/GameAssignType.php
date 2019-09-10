<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
//  Doctrine\ORM\EntityRepository;
use Scheduler\SchBundle\EventSubscriber\GameAssignSubscriber;

class GameAssignType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $subscriber = new GameAssignSubscriber($builder->getFormFactory(), $options['user'], $options['security']);
    $builder->addEventSubscriber($subscriber);
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'Scheduler\SchBundle\Entity\Game'
    ));

    $resolver->setRequired(array(
      'user',
      'security',
    ));
  }

  public function getBlockPrefix()
  {
    return 'scheduler_schbundle_gameassigntype';
  }
}
