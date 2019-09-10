<?php
namespace Cerad\ProjectTournament\Area5C;

final class StandingsCalculator
{
  private $pointsCalculator;

  public function __construct(Callable $pointsCalculator)
  {
    $this->pointsCalculator = $pointsCalculator;
  }
  private function isReportFiled($report)
  {
    return $report ? true : false;
  }
  private function calcHeadToHead($games,$team1,$team2)
  {
    $hth = 0;

    foreach($games as $game) {

      $homeGameTeam = $game['project_game_teams']['home'];
      $awayGameTeam = $game['project_game_teams']['away'];

      $report = $homeGameTeam['report'];
      if ($this->isReportFiled($report)) {
        if ($homeGameTeam['round_slot'] === $team1['round_slot'] && $awayGameTeam['round_slot'] === $team2['round_slot']) {
          if ($report['goals_scored'] > $report['goals_allowed']) $hth += 1;
          if ($report['goals_scored'] < $report['goals_allowed']) $hth -= 1;
        }
        if ($awayGameTeam['round_slot'] === $team1['round_slot'] && $homeGameTeam['round_slot'] === $team2['round_slot']) {
          if ($report['goals_scored'] > $report['goals_allowed']) $hth -= 1;
          if ($report['goals_scored'] < $report['goals_allowed']) $hth += 1;
        }
      }
    }
    return $hth;
  }
  public function __invoke($games)
  {
    // Need thist to make phpstorm happy
    $pointsCalculator = $this->pointsCalculator;

    $teamStandings = [];
    foreach($games as $game)
    {
      foreach($game['project_game_teams'] as $gameTeam)
      {
        $roundSlot = $gameTeam['round_slot'];

        $report = $gameTeam['report'];

        if (isset($teamStandings[$roundSlot])) $teamStanding = $teamStandings[$roundSlot];
        else {
          $teamStanding = [

            'round_slot'   => $roundSlot,
            'project_team' => $gameTeam['project_team'],

            'points' => 0,

            'games_scheduled' => 0,
            'games_played'    => 0,
            'games_won'       => 0,

            'goals_allowed' => 0,
            'goals_diff'    => 0,
          ];
        }
        $teamStanding['games_scheduled']++;

        if ($this->isReportFiled($report)) {

          $teamStanding['games_played']++;

          $teamStanding['points'] += $pointsCalculator($report);
          if ($report['goals_scored'] > $report['goals_allowed']) {
            $teamStanding['games_won'] += 1;
          }
          $goalsAllowed = $report['goals_allowed'];
          if ($goalsAllowed > 3) $goalsAllowed = 3;
          $teamStanding['goals_allowed'] += $goalsAllowed;

          $goalsDiff = $report['goals_scored'] - $report['goals_allowed'];
          if ($goalsDiff > 3) $goalsDiff = 3;
          //if ($goalsDiff < -3) $goalsDiff = -3; // ???
          $teamStanding['goals_diff'] += $goalsDiff;
        }
        $teamStandings[$roundSlot] = $teamStanding;
      }
    }
    $teamStandings = array_values($teamStandings);

    // Order by place
    usort($teamStandings,function($team1,$team2) use($games) {

      // Points
      if ($team1['points'] > $team2['points']) return -1;
      if ($team1['points'] < $team2['points']) return +1;

      // Head to head
      $h2h = $this->calcHeadToHead($games,$team1,$team2);
      if ($h2h > 0) return -1;
      if ($h2h < 0) return +1;

      // Wins
      if ($team1['games_won'] > $team2['games_won']) return -1;
      if ($team1['games_won'] < $team2['games_won']) return +1;

      // Goals Allowed
      if ($team1['goals_allowed'] < $team2['goals_allowed']) return -1;
      if ($team1['goals_allowed'] > $team2['goals_allowed']) return +1;

      // Goal Diff
      if ($team1['goals_diff'] > $team2['goals_diff']) return -1;
      if ($team1['goals_diff'] < $team2['goals_diff']) return +1;

      // Overall tie, use just to be consistent
      if ($team1['round_slot'] > $team2['round_slot']) return -1;
      if ($team1['round_slot'] < $team2['round_slot']) return +1;

      // Oops
      throw new \RuntimeException('Calc Team Standings');
    });

    return $teamStandings;
  }
}