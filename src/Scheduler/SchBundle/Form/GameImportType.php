<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use Scheduler\SchBundle\Entity\Game;

class GameImportType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('attachment', FileType::class, array(
          'label' => 'Filename',
          'mapped' => false,
      ))
      ->add('dryrun',CheckboxType::class, [
          'data' => true,
          'label' => 'Dry Run',
          'required' => false,
          'mapped' => false,
      ]);
  }

/* jp - doesn't use anything in Team entity
  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'Scheduler\SchBundle\Entity\Game'
    ));
  }
*/
  
  public function getBlockPrefix()
  {
    return 'scheduler_schbundle_gameimporttype';
  }
}
