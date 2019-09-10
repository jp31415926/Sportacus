<?php
namespace Cerad\Bundle\ProjectBundle\Entity;

use Scheduler\SchBundle\Entity\Game as ProjectGame;
use Scheduler\SchBundle\Entity\User as ProjectOfficial;
//  Scheduler\SchBundle\Entity\Team as ProjectTeam;

class ProjectGameOfficial implements \ArrayAccess
{
  use ArrayAccessTrait;

  const SlotReferee  = 'ref';
  const SlotReferee1 = 'ref1';
  const SlotReferee2 = 'ref2';
  const SlotAR1      = 'ar1';
  const SlotAR2      = 'ar2';
  const Slot4th      = '4th';
  const Slot5th      = '5th';
  const SlotStandby  = 'standby';

  // Maybe want different entity for these
  const SlotObserver = 'observer';
  const SlotAssessor = 'assessor';
  const SlotMentor   = 'mentor';
  const SlotMarshall = 'marshall';
  const SlotScorer   = 'scorer';
  const SlotTimer    = 'timer';
  const SlotSpotter  = 'spotter';

  protected $id;

  public $slot;

  public $projectGame;
  public $projectOfficial;

  public $assignState;
  public $assignedBy;
  public $assignorRole; // Who is allowed to assign

  public $projectTeam; // For points

  public function __construct($slot, ProjectGame $projectGame, ProjectOfficial $projectOfficial = null)
  {
    $this->slot = $slot;
    $this->projectGame     = $projectGame;
    $this->projectOfficial = $projectOfficial;
  }
  // These can eventually come from the project
  static $slotMap = [
    'ref'     => ['sort' => 1,'title' => 'Ref',],
    'ref1'    => ['sort' => 1,'title' => 'Referee 1',],
    'ref2'    => ['sort' => 2,'title' => 'Referee 2',],
    'ar1'     => ['sort' => 2,'title' => 'AR1',],
    'ar2'     => ['sort' => 3,'title' => 'AR2',],
    '4th'     => ['sort' => 4,'title' => '4TH',],
    '5th'     => ['sort' => 5,'title' => '5TH',],
    'mentor'  => ['sort' => 9,'title' => 'Mentor',],
    'standby' => ['sort' => 9,'title' => 'Standby',],
  ];
}
