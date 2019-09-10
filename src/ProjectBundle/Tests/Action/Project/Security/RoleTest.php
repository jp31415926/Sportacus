<?php
namespace Cerad\Bundle\ProjectBundle\Tests\ProjectGame\Security;

use Cerad\Bundle\ProjectBundle\Action\Project\Security\Role;

class ProjectRoleTest extends \PHPUnit_Framework_TestCase
{
  public function testRoleString()
  {
    $role = new Role('ROLE_SCHEDULER');
    $this->assertEquals('ROLE_SCHEDULER',$role->getRole());
  }
  public function testRoleAuthorizeProject()
  {
    $role = new Role('ROLE_SCHEDULER:P19:P18');

    $this->assertEquals('ROLE_SCHEDULER',$role->getRole());

    $this->assertTrue ($role->isGranted(['project' => 18]));
    $this->assertTrue ($role->isGranted(['project' => 19]));
    $this->assertTrue ($role->isGranted(['project' => [17,19,32]]));

    $this->assertFalse($role->isGranted(['project' => 20]));
    $this->assertFalse($role->isGranted(['project' => [17,32]]));
  }
  public function testRoleAuthorizeNoProject()
  {
    $role = new Role('ROLE_SCHEDULER');

    $this->assertEquals('ROLE_SCHEDULER', $role->getRole());

    $this->assertTrue($role->isGranted(['project' => 18]));
  }
  public function testRoleAuthorizeProjectRegion()
  {
    $role = new Role('ROLE_SCHEDULER:P19:P18:OR0498');

    $this->assertEquals('ROLE_SCHEDULER',$role->getRole());

    $this->assertTrue ($role->isGranted(['project' => 18, 'organization' => 'R0498']));

    $this->assertFalse($role->isGranted(['project' => 18, 'organization' => 'R0160']));

  }
  public function testRoleAuthorizeProjectRegionAge()
  {
    $role = new Role('ROLE_SCHEDULER:P19:P20:OR0160:AU16:AU19');

    $this->assertEquals('ROLE_SCHEDULER',$role->getRole());

    $this->assertTrue ($role->isGranted(['project' => 19, 'organization' => 'R0160', 'age' => 'U16']));
    $this->assertFalse($role->isGranted(['project' => 19, 'organization' => 'R0160', 'age' => 'U14']));

    $this->assertFalse($role->isGranted(['project' => 19, 'organization' => 'R0498']));

  }
}