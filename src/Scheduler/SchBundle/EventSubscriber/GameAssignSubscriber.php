<?php

namespace Scheduler\SchBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
//use Symfony\Component\HttpKernel\Event\FormEvent;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Scheduler\SchBundle\Entity\User;
use Scheduler\SchBundle\Entity\Game;
use Doctrine\ORM\EntityRepository;

class GameAssignSubscriber implements EventSubscriberInterface {

  protected $factory;
  protected $user;
  protected $security;

  public function __construct(FormFactoryInterface $factory, User $user, $security) {
    $this->factory = $factory;
    $this->user = $user;
    $this->security = $security;
  }

  public static function getSubscribedEvents() {
    // Tells the dispatcher that we want to listen on the form.pre_set_data
    // event and that the createField method should be called.
    return [FormEvents::PRE_SET_DATA => 'createField'];
  }

  public function createField(FormEvent $event) {
    $form = $event->getForm();
    $game = $event->getData();
    $uid = $this->user->getId();

    if (null === $game) {
      return;
    }

    // get array of positions
    $offpositions = $game->getProject()->getOffpositions()->toArray();
    $difficulty = $game->getAgegroup()->getDifficulty();
    $labels = [];

    foreach ($offpositions as $index => $pos) {
      if ($difficulty >= $pos->getDiffavail()) {
        $labels[] = $pos->getName();
      }
    }
    $officials = $game->getOfficials();

    // do we allow this user to change any assignment?
    $allow_any_assignment = ($this->security->isGranted('ROLE_REF_ADMIN') || $this->security->isGranted('ROLE_ASSIGNER'));

    for ($num = 0; $num < count($labels); ++$num) {
      $official = $officials[$num];
      $name = sprintf('ref%d', $num + 1);
      if ($allow_any_assignment ||
              ($game->getStatus() == Game::STATUS_NORMAL) && ((null == $official) || ($official->getId() == $this->user->getId())) && ($this->user->getRoleReferee())
      ) {
        if ($allow_any_assignment) {
          $form->add($this->factory->createNamed($name, EntityType::class, null, [
                      'class' => 'Scheduler\SchBundle\Entity\User',
                      'placeholder' => 'Unassigned',
                      'required' => false,
                      'label' => $labels[$num],
                      'auto_initialize' => false, // added for Symfony 2.3
                      'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                                        ->where('u.role_referee = 1')
                                        ->orderBy('u.last_name', 'ASC')
                                        ->addOrderBy('u.first_name', 'ASC');
                      },
          ]));
        } else {
          $form->add($this->factory->createNamed($name, EntityType::class, null, [
                      'class' => 'Scheduler\SchBundle\Entity\User',
                      'placeholder' => 'Unassigned',
                      'required' => false,
                      'label' => $labels[$num],
                      'auto_initialize' => false, // added for Symfony 2.3
                      'choices' => [$uid => $this->user]
          ]));
        }
      } else {
        if ($official) {
          $n = $official->getFullName();
        } else {
          $n = 'Unassigned';
        }
        $form->add($this->factory->createNamed($name, TextType::class, null, [
                    'attr' => ['readonly' => true],
                    'required' => false,
                    'mapped' => false,
                    'label' => $labels[$num],
                    'auto_initialize' => false, // added for Symfony 2.3
                    'data' => $n
        ]));
      }
    }
    $form->add($this->factory->createNamed('assignment_change_note', TextareaType::class, null, [
                'attr' => ['readonly' => false],
                'required' => false,
                'mapped' => false,
                'auto_initialize' => false, // added for Symfony 2.3
                'label' => 'Assignment Change Note (optional)',
    ]));
  }

}
