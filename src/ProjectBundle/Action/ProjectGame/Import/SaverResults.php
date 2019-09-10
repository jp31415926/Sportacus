<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Import;

class SaverResults
{
  public $project;

  public $errors = [];

  public $gamesAll = [];
  public $gamesCreated = [];
  public $gamesUpdated = [];
  public $gamesDeleted = [];
  public $gamesIgnored = [];
  public $gamesNoChange= [];

  protected function gameToString($prefix,$game)
  {
    $id     = isset($game['id'])     ? $game['id']     : null;
    $number = isset($game['number']) ? $game['number'] : null;

    $fieldName  = isset($game['field' ]['name']) ? $game['field'] ['name'] : 'F????';
    $levelName  = isset($game['level' ]['name']) ? $game['level'] ['name'] : 'L????';
    $regionName = isset($game['region']['name']) ? $game['region']['name'] : 'R????';

    $homeTeamName = isset($game['teams']['home']['name']) ? $game['teams']['home']['name'] : 'H????';
    $awayTeamName = isset($game['teams']['away']['name']) ? $game['teams']['away']['name'] : 'A????';

    return sprintf("%s: %d %d %s %s %s %s %s '%s' VS '%s'\n",
      $prefix,
      $id,
      $number,
      $game['date'],
      $game['time'],
      $regionName,
      $levelName,
      $fieldName,
      $homeTeamName,
      $awayTeamName
    );
  }
  public function __toString()
  {
    ob_start();

    echo sprintf("Save Errors %d, Games Total %d, Created %d, Updated %d, Deleted %d, Ignored %d\n",
      count($this->errors),
      count($this->gamesAll),
      count($this->gamesCreated),
      count($this->gamesUpdated),
      count($this->gamesDeleted),
      count($this->gamesIgnored)
    );

    if (count($this->errors)) {
      echo implode("\n",$this->errors);
      echo "\n";
    }
    foreach($this->gamesIgnored as $game) {
      echo $this->gameToString('Ignored',$game);
    }
    foreach($this->gamesDeleted as $game) {
      echo $this->gameToString('Deleted',$game);
    }
    return ob_get_clean();
  }
}