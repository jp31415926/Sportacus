<?php
namespace Cerad\ProjectTournament\Area5C;

class PointsCalculator
{
  public function __invoke($report)
  {
    $points = 0;

    // Win,lose,tie
    if ($report['goals_scored']  >  $report['goals_allowed']) $points += 6;
    if ($report['goals_scored']  <  $report['goals_allowed']) $points += 0;
    if ($report['goals_scored'] === $report['goals_allowed']) $points += 3;

    // Up to 3 points for goals scored
    $goalsScoredPoints = $report['goals_scored'];
    if ($goalsScoredPoints > 3) $goalsScoredPoints = 3;
    $points += $goalsScoredPoints;

    // Sendoffs
    $sendOffPoints = ($report['player_ejections'] * 2) + ($report['coach_ejections'] * 3);
    $points -= $sendOffPoints;

    return $points;
  }
}