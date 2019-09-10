<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class GameListCriteriaType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      //->add('project', 'entity', [
      //    'class' => 'Scheduler\SchBundle\Entity\Project',
      //    'empty_value' => 'All',
      //    'required' => false,
      //    'query_builder' => function(EntityRepository $er) {
      //        return $er->createQueryBuilder('p')
      //              ->orderBy('p.start_date', 'ASC');
      //     },]
      //)
      ->add('start_date', DateType::class, [
        'widget' => 'single_text',
        'required' => false,
      ])
      //->add('start_time', 'time', [
      //  'widget' => 'single_text',
      //  'required'  => false,
      //])
      ->add('end_date', DateType::class, [
        'widget' => 'single_text',
        'required' => false,
      ])
      //->add('end_time', 'time', [
      //  'widget' => 'single_text',
      //  'required'  => false,
      //])
      ->add('official', TextType::class, [
        'required' => false,
      ])
      ->add('team', TextType::class, [
        'label' => 'Team',
        'required' => false,
      ])
      ->add('location', TextType::class, [
        'required' => false,
      ])
      /* Disable for now
      ->add('checkForConflicts','checkbox', [
        'value' => true,
        'label' => 'Check for Conflicts',
        'required' => false,
      ])
      */
      /*
            ->add('region', 'entity', array(
                'label'     => 'Region',
                'class' => 'Scheduler\SchBundle\Entity\Region',
                'placeholder' => 'All Regions',
                'required' => false,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                          ->orderBy('u.name', 'ASC');
                 },)
            )
            */
    ;
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'Scheduler\SchBundle\Entity\GameListCriteria'
    ));
  }

  public function getBlockPrefix()
  {
    return 'scheduler_schbundle_gamelistcriteriatype';
  }
}
