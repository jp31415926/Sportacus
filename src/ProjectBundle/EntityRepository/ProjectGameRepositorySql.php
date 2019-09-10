<?php
namespace Cerad\Bundle\ProjectBundle\EntityRepository;

use Doctrine\DBAL\Connection;

class ProjectGameRepositorySql
{
  protected $dbConn;

  public function __construct(Connection $dbConn)
  {
    $this->dbConn = $dbConn;
  }
  public function getDatabaseConnection()
  {
    return $this->dbConn;
  }
  protected function extractFromRow($prefix,$row)
  {
    $len = strlen($prefix);
    $item = [];
    foreach($row as $key => $value) {
      if (substr($key,0,$len) === $prefix) {
        $item[substr($key,$len)] = $value;
      }
    }
    return $item;
  }
  /* ====================================================
   * Join Project Game Teams
   */
  protected function joinProjectGameTeams($projectGames)
  {
    $qb = $this->dbConn->createQueryBuilder();

    $qb->addSelect([
      'project_game_team.id      AS project_game_team__id',
      'project_game_team.slot    AS project_game_team__slot',
      'project_game_team.score   AS project_game_team__score',
      'project_game_team.source  AS project_game_team__source',

      'project_game_team.project_game_id  AS project_game__id',

      'project_team.id     AS project_team__id',
      'project_team.name   AS project_team__name',
      'project_team.colors_home AS project_team__colors_home',
      'project_team.colors_away AS project_team__colors_away',

      'project_team.coach_name  AS project_team__coach_name',
      'project_team.coach_email AS project_team__coach_email',
      'project_team.coach_phone AS project_team__coach_phone',
      'project_team.poc_email   AS project_team__poc_email',
    ]);
    $qb->from('project_game_teams','project_game_team');

    $qb->leftJoin(
      'project_game_team',
      'Team',
      'project_team',
      'project_team.id  = project_game_team.project_team_id'
    );

    $qb->andWhere('project_game_team.project_game_id IN (?)');

    $stmt = $this->dbConn->executeQuery($qb->getSQL(),[array_keys($projectGames)],[Connection::PARAM_INT_ARRAY]);

    while($row = $stmt->fetch()) {

      $projectGameTeam = $this->extractFromRow('project_game_team__',$row);

      $projectGameId = $row['project_game__id'];

      $projectTeam = $this->extractFromRow('project_team__',$row);

      $projectGameTeam['project_team'] = $projectTeam;

      $projectGames[$projectGameId]['project_game_teams'][$projectGameTeam['slot']] = $projectGameTeam;
    }
    return $projectGames;
  }
  /* ====================================================
   * Join Project Game Teams
   */
  protected function joinProjectGameOfficials($projectGames)
  {
    $qb = $this->dbConn->createQueryBuilder();

    $qb->addSelect([
      'game_official.id      AS game_official__id',
      'game_official.slot    AS game_official__slot',

      'game_official.project_game_id AS game__id',

      'official.id         AS official__id',
      'official.username   AS official__username',
      'official.first_name AS official__name_first',
      'official.last_name  AS official__name_last',
      'official.email      AS official__email',

      'official.phone_mobile          AS official__phone_cell',
      'official.option_reminder_text  AS official__option_reminder_text',
      'official.option_reminder_email AS official__option_reminder_email',

    ]);
    $qb->from('project_game_officials','game_official');

    $qb->leftJoin(
      'game_official',
      'fos_user',
      'official',
      'official.id  = game_official.project_official_id'
    );
    $qb->andWhere('game_official.project_game_id IN (?)');

    $stmt = $this->dbConn->executeQuery($qb->getSQL(),[array_keys($projectGames)],[Connection::PARAM_INT_ARRAY]);

    while($row = $stmt->fetch()) {

      $gameOfficial = $this->extractFromRow('game_official__',$row);

      $gameId = $row['game__id'];

      $gameOfficial['project_official'] = $this->extractFromRow('official__',$row);

      $projectGames[$gameId]['project_game_officials'][$gameOfficial['slot']] = $gameOfficial;
    }
    return $projectGames;
  }
  protected function createProjectGameQueryBuilder()
  {
    $qb = $this->dbConn->createQueryBuilder();

    $qb->addSelect([
      'game.id         AS game__id',
      'game.number     AS game__number',
      'game.date       AS game__date',
      'game.time       AS game__time',
      'game.status     AS game__status',
      'game.published  AS game__published',
      'game.short_note AS game__short_note',

      'game.length         AS game__length',
      'game.timeslotlength AS game__timeslotlength',

      'level.id      AS level__id',
      'level.name    AS level__age',
      'region.id     AS region__id',
      'region.name   AS region__name',
      'project.id    AS project__id',
      'project.name  AS project__name',

      'location.id        AS location__id',
      'location.name      AS location__name',
      'location.long_name AS location__long_name',
    ]);

    $qb->from('Game','game');

    $qb->leftJoin('game','Region',  'region',  'region.id   = game.region_id');
    $qb->leftJoin('game','Project', 'project', 'project.id  = game.project_id');
    $qb->leftJoin('game','Location','location','location.id = game.location_id');
    $qb->leftJoin('game','AgeGroup','level',   'level.id    = game.agegroup_id');

    return $qb;
  }
  protected function loadProjectGames($qb) // Change this to accept statement
  {
    $stmt = $qb->execute();

    $projectGames = [];
    while($row = $stmt->fetch()) {

      $game = $this->extractFromRow('game__',$row);

      $game['level']    = $this->extractFromRow('level__',   $row);
      $game['region']   = $this->extractFromRow('region__',  $row);
      $game['project']  = $this->extractFromRow('project__', $row);
      $game['location'] = $this->extractFromRow('location__',$row);

      $game['project_game_teams'    ] = [];
      $game['project_game_officials'] = [];

      $projectGames[$game['id']] = $game;
    }
    $projectGames = $this->joinProjectGameTeams    ($projectGames);
    $projectGames = $this->joinProjectGameOfficials($projectGames);

    return $projectGames;
  }
  /* ==================================================
   * Load 0 or more games for array of game ids
   * Indexed by game ids
   *
   * Little bit awkward because of the IN statement, need to use executeQuery
   */
  public function findForIds(array $ids)
  {
    if (count($ids) < 1) return [];

    $qb = $this->createProjectGameQueryBuilder();

    $qb->andWhere('game.id IN (?)');

    $qb->addOrderBy('game.date');
    $qb->addOrderBy('game.time');
    $qb->addOrderBy('location.name');
    $stmt = $this->dbConn->executeQuery($qb->getSQL(),[$ids],[Connection::PARAM_INT_ARRAY]);

    $games = [];
    while($row = $stmt->fetch()) {

      $game = $this->extractFromRow('game__',$row);

      $game['level']    = $this->extractFromRow('level__',   $row);
      $game['region']   = $this->extractFromRow('region__',  $row);
      $game['project']  = $this->extractFromRow('project__', $row);
      $game['location'] = $this->extractFromRow('location__',$row);

      $game['project_game_teams'    ] = [];
      $game['project_game_officials'] = [];

      $games[$game['id']] = $game;
    }
    $games = $this->joinProjectGameTeams    ($games);
    $games = $this->joinProjectGameOfficials($games);

    return $games;
  }
  public function findOne($id)
  {
    $qb = $this->createProjectGameQueryBuilder();

    $qb->where('game.id = :id');
    $qb->setParameter('id',$id);

    $projectGames = $this->loadProjectGames($qb);

    return count($projectGames) === 1 ? array_pop($projectGames) : null;
  }
  public function findOneForProjectNumber($projectId,$number)
  {
    $qb = $this->createProjectGameQueryBuilder();

    $qb->where('game.project_id = ? AND game.number = ?');
    $qb->setParameter(0,$projectId);
    $qb->setParameter(1,$number);

    $projectGames = $this->loadProjectGames($qb);

    return count($projectGames) === 1 ? array_pop($projectGames) : null;
  }
  public function maxGameNumber($projectId)
  {
    $sql = 'SELECT MAX(number) AS max FROM Game WHERE project_id = ?;';
    $stmt = $this->dbConn->executeQuery($sql,[$projectId]);
    $rows = $stmt->fetchAll();
    return count($rows) === 0 ? null : $rows[0]['max'];
  }
  public function deleteForProjectNumber($projectId,$number)
  {
    $dbConn = $this->dbConn;

    $sql = 'SELECT DISTINCT(id) AS id FROM Game WHERE project_id = ? AND number = ?';
    $stmt = $dbConn->executeQuery($sql,[$projectId,$number]);
    $rows = $stmt->fetchAll();
    $id = count($rows) === 1 ? $rows[0]['id'] : null;
    if (!$id) return 0;

    $dbConn->executeUpdate('DELETE FROM project_game_teams     WHERE project_game_id = ?;',[$id]);
    $dbConn->executeUpdate('DELETE FROM project_game_officials WHERE project_game_id = ?;',[$id]);

    return $dbConn->executeUpdate('DELETE FROM Game WHERE id = ?;',[$id]);
  }
  /* =========================================================
   * query for a distinct list of game ids that match the criteria
   * Do this because
   *   1. The many to many relations search capability requires it
   *   2. Needed for pagination(limit and offset)
   *   3. Often needed for more complex sorts and group bys
   *
   * And just to be difficult, turn criteria into an array
   *
   * And to really make it fun and performant, use sql
  */
  protected function joinOfficials($qb,$criteria)
  {
    $official   = isset($criteria['official'])    ? $criteria['official'] :    null;
    $officialId = isset($criteria['official_id']) ? $criteria['official_id'] : null;

    if (!$official && !$officialId) return;

    $qb->leftJoin(
      'project_game',
      'project_game_officials',
      'project_game_official',
      'project_game_official.project_game_id = project_game.id'
    );
    $qb->leftJoin(
      'project_game_official',
      'fos_user',
      'project_official',
      'project_game_official.project_official_id = project_official.id'
    );
    if ($official) {
      $where = "(CONCAT(project_official.first_name, ' ', project_official.last_name) LIKE :official)";
      $qb->andWhere($where);
      $qb->setParameter('official','%' . $official . '%');
    }
    if ($officialId) {
      $qb->andWhere(    'project_official.id = :project_official_id');
      $qb->setParameter('project_official_id',$officialId);
    }
  }
  public function findGameIdsByCriteria($criteria = [])
  {
    $qb = $this->dbConn->createQueryBuilder();

    $qb->addSelect('distinct project_game.id AS id');

    $qb->from('Game','project_game');

    if (isset($criteria['project']) && $criteria['project']) {
      $project = $criteria['project'];
      $projectId = is_object($project) ? $project->getId() : $project;
      $qb->andWhere('project_game.project_id = :project_id');
      $qb->setParameter('project_id',$projectId);
    }
    if (isset($criteria['project_id']) && $criteria['project_id']) {
      $qb->andWhere('project_game.project_id = :project_id');
      $qb->setParameter('project_id',$criteria['project_id']);
    }
    if (isset($criteria['status_id']) && $criteria['status_id']) {
      $qb->andWhere('project_game.status = :status_id');
      $qb->setParameter('status_id',$criteria['status_id']);
    }
    // TOD0 Should be able to handle array of numbers
    if (isset($criteria['number']) && $criteria['number']) {
      $number = $criteria['number'];
      $qb->andWhere('project_game.number = :number');
      $qb->setParameter('number',$number);
    }
    if (isset($criteria['date_start']) && $criteria['date_start']) {
      $qb->andWhere('project_game.date BETWEEN :date_start AND :date_end');
      $qb->setParameter('date_start',$criteria['date_start']);
      $qb->setParameter('date_end',  $criteria['date_end']);
    }
    if (isset($criteria['location']) && $criteria['location']) {
      $qb->leftJoin('project_game','Location','project_location','project_location.id = project_game.location_id');
      $qb->andWhere('project_location.name LIKE :location');
      $qb->setParameter('location','%' . $criteria['location'] . '%');
    }
    if (isset($criteria['only_published']) && $criteria['only_published']) {
      $qb->andWhere('project_game.published = 1');
    }
    if (isset($criteria['published']) && $criteria['published']) {
      $qb->andWhere('project_game.published = 1');
    }
    // awkward but needed for bc
    $teamSearch  = isset($criteria['team' ]) ? $criteria['team' ] : null;
    $coachSearch = isset($criteria['coach']) ? $criteria['coach'] : null;
    $teamSearch = $teamSearch ? $teamSearch : $coachSearch;
    if ($teamSearch)
    {
      $qb->leftJoin(
        'project_game',
        'project_game_teams',
        'project_game_team',
        'project_game_team.project_game_id = project_game.id'
      );
      $qb->leftJoin(
        'project_game_team',
        'Team', // project_teams
        'project_team',
        'project_game_team.project_team_id = project_team.id'
      );
      $qb->andWhere('(project_team.name LIKE :team OR project_team.coach_name LIKE :team)');
      $qb->setParameter('team','%' . $teamSearch . '%');
    }
    $this->joinOfficials($qb,$criteria);

    $stmt = $qb->execute();
    $ids = [];
    while($row = $stmt->fetch()) {
      $ids[] = $row['id'];
    }
    return $ids;
  }
}
