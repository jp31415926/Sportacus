<?php
// src/Scheduler/SchBundle/Form/EnquiryType.php

namespace Scheduler\SchBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EnquiryType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('name');
    $builder->add('email', EmailType::class);
    $builder->add('subject');
    $builder->add('body', TextareaType::class);
  }

  public function getBlockPrefix()
  {
    return 'contact';
  }
}

