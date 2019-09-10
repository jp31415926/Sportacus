<?php
namespace Cerad\Bundle\ProjectBundle\Entity;

/**
 * This is a value object currently stored as the game_team report property
 * The ng2014 object had some helper methods which is why it's an object
 * But might just stick with an array
 *
 * Trying to avoid the slow key_exists function hence the true stuff
 */
final class ProjectGameTeamReport implements \ArrayAccess
{
  static $keys = [
    'project_game_team' => true,  // Not sure want this

    'goals_scored'  => true,
    'goals_allowed' => true,

    'points_earned' => true,
    'points_minus'  => true,

    'sportsmanship' => true,
    'fudge_factor'  => true,

    'player_warnings'  => true,
    'player_ejections' => true,
    'coach_warnings'   => true,
    'coach_ejections'  => true,
    'bench_warnings'   => true,
    'bench_ejections'  => true,
    'spec_warnings'    => true,
    'spec_ejections'   => true,
  ];
  private $data = [];

  public function __construct(array $params = [])
  {
    foreach(self::$keys as $key)
    {
      $this->data[$key] = isset($params[$key]) ? $params[$key] : null;
    }
  }
  public function getData()
  {
    return $this->data;
  }
  /** ===========================================================
   * ArrayAccess
   */
  public function offsetGet($key)
  {
    if (isset(self::$keys[$key])) {
      return $this->data[$key];
    }
    throw new \UnexpectedValueException("__METHOD__} {$key}");
  }
  public function offsetSet($key,$value)
  {
    if (isset(self::$keys[$key])) {
      return $this->data[$key] = $value;
    }
    throw new \UnexpectedValueException("{__METHOD__} {$key} {$value}");
  }
  public function offsetExists($key)
  {
    if (isset(self::$keys[$key])) {
      return true;
    }
    return false;
  }
  public function offsetUnset($key)
  {
    throw new \BadMethodCallException("{__METHOD__} {$key}");
  }

}