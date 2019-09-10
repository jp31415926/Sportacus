<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
//use Doctrine\ORM\EntityRepository;
//use Scheduler\SchBundle\EventSubscriber\GameAssignSubscriber;
use Scheduler\SchBundle\Entity\Game;

class GameScorecardType extends AbstractType {

  public function buildForm(FormBuilderInterface $builder, array $options) {
    /* @var $readonly boolean */
    $readonly = $options['readonly'];
    $builder
            ->add('status', ChoiceType::class, [
                'choices' => Game::getStatusValues2(),
//                'choices_as_values' => true,
                'attr' => ['readonly' => $readonly],
            ])
            ->add('score1', IntegerType::class, [
                'label' => 'Home Score',
                'required' => false,
                'attr' => ['readonly' => $readonly],
            ])
            ->add('score2', IntegerType::class, [
                'label' => 'Away Score',
                'required' => false,
                'attr' => ['readonly' => $readonly],
            ])
            ->add('ref_notes', TextareaType::class, [
                'label' => 'Scorecard Notes',
                'required' => false,
                'attr' => ['readonly' => $readonly],
            ])
            ->add('alert_admin', CheckboxType::class, [
                'label' => 'Alert Admin',
                'required' => false,
                'attr' => ['readonly' => $readonly],
    ]);
  }

  public function configureOptions(OptionsResolver $resolver) {
    $resolver->setDefaults([
        'data_class' => 'Scheduler\SchBundle\Entity\Game'
    ]);

    $resolver->setRequired([
        'readonly',
    ]);
  }

  public function getBlockPrefix() {
    return 'scheduler_schbundle_scorecardtype';
  }

}
