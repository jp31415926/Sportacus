<?php
namespace Cerad\Bundle\ProjectBundle\Entity;

trait ProjectLevelTrait
{
  public function offsetSet($offset, $value) {
    switch($offset) {

      case 'age':
        /** @noinspection PhpUndefinedFieldInspection */
        $this->name = $value;
        return;

    }
    $this->$offset = $value;
  }
  public function offsetGet($offset) {
    switch($offset) {

      case 'age':
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->name;

    }
    return $this->$offset;
  }
  public function offsetExists($offset) {
    switch($offset) {

      case 'age':
        /** @noinspection PhpUndefinedFieldInspection */
        return isset($this->name);
    }
    return isset($this->$offset);
  }
  public function offsetUnset($offset) {
    switch($offset) {

      case 'age':
        /** @noinspection PhpUndefinedFieldInspection */
        $this->name = null;
        return;

    }
    $this->$offset = null;
  }

}