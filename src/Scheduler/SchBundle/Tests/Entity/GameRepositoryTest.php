<?php

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GameRepositoryTest extends WebTestCase
{
  public function testFindOfficialGameConflicts()
  {
    $client = static::createClient();
    $dic = $client->getContainer();
    $repo = $dic->get('sportacus_project_game_repository');

    // Hard code one of my game for now
    $game = $repo->find(8444);
    $this->assertEquals('2015-08-22',$game['date']->format('Y-m-d'));
    $this->assertEquals('12:30:00',  $game['time']->format('H:i:s'));
    $this->assertEquals('13:44:59',  $game['end_time']->format('H:i:s'));

    // Fake Conflict with game 103
    $official = ['id' => 36];

    $conflicts = $repo->findOfficialGameConflicts($game, $official);
    $this->assertEquals(2,count($conflicts));
    $this->assertEquals(103,$conflicts[0]['number']);
  }
  public function testFindTeamGameConflicts()
  {
    $client = static::createClient();
    $dic = $client->getContainer();
    $repo = $dic->get('sportacus_project_game_repository');

    // Game #563
    $game = $repo->find(8444);

    // Fake Conflict with game #1392
    $team = ['id' => 3006];

    $conflicts = $repo->findTeamGameConflicts($game, $team);
    $this->assertEquals(1,count($conflicts));
    $this->assertEquals(1392,$conflicts[0]['number']);
  }
}