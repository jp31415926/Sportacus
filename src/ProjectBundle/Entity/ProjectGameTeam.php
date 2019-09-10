<?php
namespace Cerad\Bundle\ProjectBundle\Entity;

use Scheduler\SchBundle\Entity\Game as ProjectGame;
use Scheduler\SchBundle\Entity\Team as ProjectTeam;

class ProjectGameTeam implements \ArrayAccess
{
  use ArrayAccessTrait;

  const SlotHome = 'home';
  const SlotAway = 'away';

  protected $id;

  public $slot;

  public $projectGame;
  public $projectTeam;
  public $source;
  public $score;

  public $projectLevel;

  public $roundType;
  public $roundName;
  public $roundSlot;
  public $conduct;

  public function __construct($slot, ProjectGame $projectGame, ProjectTeam $projectTeam = null, $source = null)
  {
    $this->slot   = $slot;
    $this->source = $source;
    $this->projectGame = $projectGame;
    $this->projectTeam = $projectTeam;
  }
  // This can eventually come from the project
  static $slotMap = [
    'home' => ['sort' => 1,'title' => 'Home Team',],
    'away' => ['sort' => 2,'title' => 'Away Team',],
  ];
}
