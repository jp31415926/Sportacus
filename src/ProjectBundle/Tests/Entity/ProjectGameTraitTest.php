<?php
namespace Cerad\Bundle\ProjectBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Scheduler\SchBundle\Entity\Game;

class ProjectGameTraitTest extends WebTestCase
{
  public function testNumberTrait()
  {
    $game = new Game();
    $game->setNumber(999);
    $this->assertEquals(999,$game->getNumber());
  }
  public function testProjectGamesTrait()
  {
    $game = new Game();
    $game->setNumber(999);
    $this->assertEquals(999,$game->getNumber());
  }
}
