<?php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class TeamType extends AbstractType {

  public function buildForm(FormBuilderInterface $builder, array $options) {
    $agegroups = $options['agegroups'];
    $builder
            ->add('name')
            ->add('coach')
            ->add('coachphone', TextType::class, [
                'required' => false,
            ])
            ->add('coachemail', TextType::class, [
                'label' => 'Coach Email',
                'required' => false,
            ])
            ->add('pocemail', TextType::class, [
                'label' => 'Alt POC Email',
                'required' => false,
            ])
            ->add('colorshome', TextType::class, [
                'label' => 'Home Colors',
                'required' => false,
            ])
            ->add('colorsaway', TextType::class, [
                'label' => 'Away Colors',
                'required' => false,
            ])
            ->add('agegroup', EntityType::class, [
                'class' => 'Scheduler\SchBundle\Entity\AgeGroup',
                'placeholder' => '',
                'required' => false,
                'choices' => $agegroups,
            ])
            ->add('region', EntityType::class, [
                'class' => 'Scheduler\SchBundle\Entity\Region',
                'placeholder' => '',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                  return $er->createQueryBuilder('u')
                          ->orderBy('u.name', 'ASC');
                }
            ])
            ->add('project');
  }

  public function configureOptions(OptionsResolver $resolver) {
    $resolver->setDefaults([
        'data_class' => 'Scheduler\SchBundle\Entity\Team'
    ]);

    $resolver->setRequired([
        'agegroups',
    ]);
  }

  public function getBlockPrefix() {
    return 'scheduler_schbundle_teamtype';
  }

}
