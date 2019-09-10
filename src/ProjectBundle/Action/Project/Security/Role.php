<?php
namespace Cerad\Bundle\ProjectBundle\Action\Project\Security;

use Symfony\Component\Security\Core\Role\RoleInterface;

class Role implements RoleInterface
{
  protected $role;
  protected $grants = [];

  public function __construct($role)
  {
    $parts = explode(':',$role);

    // First element is the usual role string
    $this->role = array_shift($parts);

    foreach($parts as $part) {
      switch($part[0]) {
        case 'P':
          $this->grants['project'][] = substr($part,1);
          break;
        case 'O':
          $this->grants['organization'][] = substr($part,1);
          break;
        case 'A':
          $this->grants['age'][] = substr($part,1);
          break;
        case 'L':
          $this->grants['location'][] = substr($part,1);
          break;
      }
    }

  }
  public function getRole()
  {
    return $this->role;
  }
  public function isGranted(array $criteria)
  {
    $grants = $this->grants;
    foreach($criteria as $key => $values)
    {
      $values = is_array($values) ? $values : [$values];

      if (isset($grants[$key])) { //print_r($values); print_r($grants[$key]);
        $granted = false;
        foreach($values as $value) {
          if (in_array($value,$grants[$key])) {
            $granted = true;
            continue;
          }
        }
        if (!$granted) return false;
      }
      //elseif (count($values)) return false;
    }
    return true;
  }
}