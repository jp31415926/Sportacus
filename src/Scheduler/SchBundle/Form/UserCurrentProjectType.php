<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class UserCurrentProjectType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('currentproject', EntityType::class, [
          'class' => 'Scheduler\SchBundle\Entity\Project',
          'label' => 'Current Project',
          'required' => true,
          'query_builder' => function (EntityRepository $er) {
            return $er->createQueryBuilder('p')
							->where('p.archived = ?1')
              ->orderBy('p.start_date', 'DESC')
              ->orderBy('p.end_date', 'DESC')
							->setParameter(1, 0)
              ;
					},
      ]);
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults([
      'data_class' => 'Scheduler\SchBundle\Entity\User'
    ]);
  }

  public function getBlockPrefix()
  {
    return 'scheduler_schbundle_usercurrentprojecttype';
  }
}
