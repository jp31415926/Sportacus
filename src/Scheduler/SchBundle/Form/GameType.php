<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Scheduler\SchBundle\Entity\Game;
//use Scheduler\SchBundle\Entity\Team;

class GameType extends AbstractType {

  public function buildForm(FormBuilderInterface $builder, array $options) {
    $teams = $options['teams'];
    $agegroups = $options['agegroups'];
    $builder
            ->add('published', CheckboxType::class, [
                'label' => 'Published?',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'choices' => Game::getStatusValues2(),
//                'choices_as_values' => true,
            ])
            ->add('agegroup', EntityType::class, [
                'class' => 'Scheduler\SchBundle\Entity\AgeGroup',
                'placeholder' => '',
                'required' => false,
                'choices' => $agegroups,
            ])
            ->add('team1', EntityType::class, [
                'label' => 'Home Team',
                'class' => 'Scheduler\SchBundle\Entity\Team',
                'placeholder' => '',
                'required' => false,
                'choices' => $teams,
            ])
            ->add('team2', EntityType::class, [
                'label' => 'Away Team',
                'class' => 'Scheduler\SchBundle\Entity\Team',
                'placeholder' => '',
                'required' => false,
                'choices' => $teams,
            ])
            //->add('idstr')
            ->add('number')
            ->add('date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('time', TimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('length')
            ->add('timeslotlength', IntegerType::class, [
                'label' => 'Timeslot Length',
                'required' => false,
            ])
            ->add('location', EntityType::class, [
                'class' => 'Scheduler\SchBundle\Entity\Location',
                'placeholder' => '',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('u')
                          ->orderBy('u.name', 'ASC');
                },
            ])
            ->add('short_note')
            ->add('region', EntityType::class, [
                'class' => 'Scheduler\SchBundle\Entity\Region',
                'placeholder' => '',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('u')
                          ->orderBy('u.name', 'ASC');
                },
            ])
            ->add('project')
            ->add('ref1', EntityType::class, [
                'class' => 'Scheduler\SchBundle\Entity\User',
                'placeholder' => '',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('u')
                          ->where('u.role_referee = 1')
                          ->orderBy('u.last_name', 'ASC')
                          ->addOrderBy('u.first_name', 'ASC');
                },
            ])
            ->add('ref2', EntityType::class, [
                'class' => 'Scheduler\SchBundle\Entity\User',
                'placeholder' => '',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('u')
                          ->where('u.role_referee = 1')
                          ->orderBy('u.last_name', 'ASC')
                          ->addOrderBy('u.first_name', 'ASC');
                },
            ])
            ->add('ref3', EntityType::class, [
                'class' => 'Scheduler\SchBundle\Entity\User',
                'placeholder' => '',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('u')
                          ->where('u.role_referee = 1')
                          ->orderBy('u.last_name', 'ASC')
                          ->addOrderBy('u.first_name', 'ASC');
                },
            ])
            ->add('ref4', EntityType::class, [
                'class' => 'Scheduler\SchBundle\Entity\User',
                'placeholder' => '',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('u')
                          ->where('u.role_referee = 1')
                          ->orderBy('u.last_name', 'ASC')
                          ->addOrderBy('u.first_name', 'ASC');
                },
            ])
            ->add('ref5', EntityType::class, [
                'class' => 'Scheduler\SchBundle\Entity\User',
                'placeholder' => '',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('u')
                          ->where('u.role_referee = 1')
                          ->orderBy('u.last_name', 'ASC')
                          ->addOrderBy('u.first_name', 'ASC');
                },
            ])
            ->add('score1', IntegerType::class, [
                'label' => 'Home Score',
                'required' => false,
            ])
            ->add('score2', IntegerType::class, [
                'label' => 'Away Score',
                'required' => false,
            ])
            ->add('alert_admin', CheckboxType::class, [
                'label' => 'Alert Admin?',
                'required' => false,
            ])
            ->add('ref_notes', TextareaType::class, [
                'label' => 'Referee Scorecard Notes',
                'required' => false,
    ]);
  }

  public function configureOptions(OptionsResolver $resolver) {
    $resolver->setDefaults([
        'data_class' => 'Scheduler\SchBundle\Entity\Game'
    ]);

    $resolver->setRequired([
        'teams',
        'agegroups',
    ]);
  }

  public function getBlockPrefix() {
    return 'scheduler_schbundle_gametype';
  }

}
