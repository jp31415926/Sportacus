<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Import;

use Doctrine\DBAL\Connection;
use Cerad\Bundle\ProjectBundle\EntityRepository\ProjectGameRepositorySql as GameRepository;

class SaverSql
{
  protected $dbConn;
  protected $repository;

  public function __construct(GameRepository $repository)
  {
    $this->repository = $repository;
    $this->dbConn     = $repository->getDatabaseConnection();
  }
  /* =============================================================
   * CRUD the extracted game
   *
   */
  protected function processGame($results,$game)
  {
    // Set default game number
    $projectId = $game['project_id'];
    $number    = $game['number'];
    if ($number < 0) {
      return $this->deleteGame($results,$game);
    }
    // Expect everything to have a number but maybe not
    if (!$number) {

      // Ignore if already have a game in the slot
      $fieldId = isset($game['field']['id']) ? $game['field']['id'] : null;
      $gameId = $this->findGameForDateTimeSlot($game['date'], $game['time'], $fieldId);
      if ($gameId) {
        $game['id'] = $gameId;
        $results->gamesIgnored[] = $game;
        //echo sprintf("Existing game %d\n",$gameId);
        return;
      }

      // Will this work if games were not committed?
      $game['number'] = $number = $this->repository->maxGameNumber($projectId) + 1;
      //echo sprintf("Next Number %d\n",$number);
    }
    else {
      // See if already have one
      $gameExisting = $this->repository->findOneForProjectNumber($projectId, $number);

      if ($gameExisting) {
        return $this->updateGame($results,$game,$gameExisting);
      }
    }
    // Messy stuff is over, have a new game

    // 'NOW()' does not work
    $now = (new \DateTime('now'))->format('Y-m-d H:i:s');

    $gameNew = [
      'number' => $number,
      'date' => $game['date'],
      'time' => $game['time'],

      'project_id'  => $projectId,
      'season_id'   => $projectId,

      'agegroup_id' => isset($game['level' ]['id']) ? $game['level' ]['id'] : null,
      'location_id' => isset($game['field' ]['id']) ? $game['field' ]['id'] : null,
      'region_id'   => isset($game['region']['id']) ? $game['region']['id'] : null,

      'length'         => $game['length'],
      'timeslotlength' => $game['timeslotlength'],
      'published'      => $game['published'],
      'status'         => $game['status'],
      'short_note'     => $game['short_note'],

      'created' => $now,
      'updated' => $now,
    ];
    $dbConn = $this->dbConn;
    $dbConn->insert('Game',$gameNew);

    $gameId = $this->dbConn->lastInsertId();

    // Teams
    foreach($game['teams'] as $slot => $team) {
      $projectTeam = [
        'slot' => $slot,
        'project_game_id' => $gameId,
        'project_team_id' => $team['id'],
      ];
      $dbConn->insert('project_game_teams',$projectTeam);
    }
    // Officials
    foreach($game['officials'] as $slot => $official) {
      $projectOfficial = [
        'slot' => $slot,
        'project_game_id' => $gameId,
      ];
      $dbConn->insert('project_game_officials',$projectOfficial);
    }
    // Stash the complete graph
    $results->gamesCreated[] = $this->repository->findOne($gameId);
  }
  /* ================================================================
   * Check and update game changes
   * Not really sure how much need to worry about quoting
   * Already validated most of the fields
   *
   * Something funny with integer comparisons
   *
   * Need to handle updated and updated by better
   */
  protected function updateGame($results,$game,$gameExisting)
  {
    $gameId = $gameExisting['id'];

    $dbConn = $this->dbConn;
    $updated = false;
    $changes = [];

    // Integers
    foreach(['status','length','timeslotlength','published',] as $key) {
      $val1 = (integer)$game[$key];
      $val2 = (integer)$gameExisting[$key];
      if ($val1 !== $val2) {
        $changes[$key] = $val1;
      }
    }
    // Strings and nulls?
    foreach(['date','time','short_note',] as $key) {
      $val1 = (string)$game[$key];
      $val2 = (string)$gameExisting[$key];
      if ($val1 !== $val2) {
        // This ends up adding quote marks
        // $changes[$key] = $dbConn->quote($val1,\PDO::PARAM_STR);

        // Do I need to escape things?  O'Conner works fine
        $changes[$key] = $val1;
      }
    }
    // Relations, handling null is kind of tricky
    $id1 = isset($game        ['field']   ['id']) ? $game        ['field']   ['id'] : null;
    $id2 = isset($gameExisting['location']['id']) ? $gameExisting['location']['id'] : null;
    if ($id1 !== $id2) {
      $changes['location_id'] = $id1;
    }
    $id1 = isset($game        ['region']['id']) ? $game        ['region']['id'] : null;
    $id2 = isset($gameExisting['region']['id']) ? $gameExisting['region']['id'] : null;
    if ($id1 !== $id2) {
      $changes['region_id'] = $id1;
    }
    $id1 = isset($game        ['level']['id']) ? $game        ['level']['id'] : null;
    $id2 = isset($gameExisting['level']['id']) ? $gameExisting['level']['id'] : null;
    if ($id1 !== $id2) {
      $changes['agegroup_id'] = $id1;
    }

    // Persist game changes
    if (count($changes))
    {
      $changes['updated'] = (new \DateTime('now'))->format('Y-m-d H:i:s');

      $this->dbConn->update('Game',$changes,['id' => $gameId]);
      $updated = true;

      //print_r($changes);
      //echo sprintf("%d %d %d\n",$gameId,$game['status'],$gameExisting['status']);
      //die();
    }
    // Check teams
    foreach(['home','away'] as $slot)
    {
      $changes = [];

      $team1Id = isset($game['teams'][$slot]['id']) ?
                       $game['teams'][$slot]['id'] : null;

      $team2Id = isset($gameExisting['project_game_teams'][$slot]['project_team']['id']) ?
                       $gameExisting['project_game_teams'][$slot]['project_team']['id']  : null;

//echo sprintf("Team %d %s %d %d\n",$gameId,$slot,$team1Id,$team2Id);

      if ($team1Id !== $team2Id) {
        $changes['project_team_id'] = $team1Id;
      }
      if (count($changes)) {
        $id =  $gameExisting['project_game_teams'][$slot]['id'];
        $this->dbConn->update('project_game_teams',$changes,['id' => $id]);
        $updated = true;
      }
    }
    /* Stash any updates */
    if ($updated) {

      // Maybe grab the complete graph?
      $results->gamesUpdated[] = [
        'new' => $game,
        'old' => $gameExisting,
        'changes' => $changes,
      ];
    }
    return;
  }
  /* ================================================================
   * Delete the game is it exists
   *
   */
  protected function deleteGame($results,$game)
  {
    $count = $this->repository->deleteForProjectNumber($game['project_id'],abs($game['number']));

    if ($count) {
      $results->gamesDeleted[] = $game;
    }
  }
  /* ================================================================
   * Mostly to protect against duplicating games during initial import
   * with no project number (or possibly id)
   */
  protected function findGameForDateTimeSlot($date,$time,$fieldId)
  {
    $sql  = 'SELECT id FROM Game where date = ? AND time = ? AND location_id = ?';
    $stmt = $this->dbConn->executeQuery($sql,[$date,$time,$fieldId]);
    $rows = $stmt->fetchAll();
    return count($rows) === 0 ? null : $rows[0]['id'];
  }
  /* =========================================================
   * Main enntry point
   *
   */
  public function save($games)
  {
    $results = new SaverResults();
    $results->gamesAll = $games;

    // CRUD them
    foreach($games as $game) {
      $this->processGame($results,$game);
    }

    return $results;
  }
}
