<?php
namespace Cerad\Bundle\ProjectBundle\Entity;

class ProjectLevel implements \ArrayAccess
{
  private $id;
  private $project;
  private $level_key;
  private $name;
  private $title;
  private $age;
  private $gender;
  private $division;

  // Defaults
  private $game_slot_length;
  private $crew_type; // dsc, dsc4, dual, solo, none

  // ArrayAccess
  public function offsetSet($offset, $value) {
    $this->$offset = $value;
  }
  public function offsetGet($offset) {
    return $this->$offset;
  }
  public function offsetExists($offset) {
    return isset($this->$offset);
  }
  public function offsetUnset($offset) {
    $this->$offset = null;
  }

}