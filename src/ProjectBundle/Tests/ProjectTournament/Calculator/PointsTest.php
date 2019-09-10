<?php

use Symfony\Component\Yaml\Yaml;

use Cerad\ProjectTournament\Area5C\PointsCalculator as PointsCalculatorArea5C;

class PointsTest extends \PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    $this->reports = Yaml::parse(file_get_contents(__DIR__ . '/reports.yml'));
  }
  public function testArea5C()
  {
    $calc = new PointsCalculatorArea5C();

    foreach($this->reports['area5c'] as $report) {
      $points = $calc($report);
      $this->assertEquals($report['points'], $points);
    }
  }
}