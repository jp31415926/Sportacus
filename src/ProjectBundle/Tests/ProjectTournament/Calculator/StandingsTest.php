<?php

use Symfony\Component\Yaml\Yaml;

use Cerad\ProjectTournament\Area5C\PointsCalculator    as PointsCalculatorArea5C;
use Cerad\ProjectTournament\Area5C\StandingsCalculator as StandingsCalculatorArea5C;

class StandingsTest extends \PHPUnit_Framework_TestCase
{
  private $games;

  public function setUp()
  {
    $this->games = Yaml::parse(file_get_contents(__DIR__ . '/games.yml'));
  }
  public function testArea5C()
  {
    $calc = new StandingsCalculatorArea5C(new PointsCalculatorArea5C());

    $standings = $calc($this->games['U12B_A']);

    $this->assertEquals('A1',$standings[0]['round_slot']);
    $this->assertEquals('A3',$standings[1]['round_slot']);
    $this->assertEquals('A2',$standings[2]['round_slot']);

    //print_r($standings);

  }
}
