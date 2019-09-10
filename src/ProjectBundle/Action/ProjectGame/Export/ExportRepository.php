<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Export;

use Doctrine\DBAL\Connection;
use Scheduler\SchBundle\Entity\Game as ProjectGame;

class ExportRepository
{
  protected $dbConn;

  public function __construct(Connection $dbConn)
  {
    $this->dbConn = $dbConn;
  }
  // This should use the standard project game repository
  public function loadProjectGames($criteria)
  {
    $projectId = $criteria['project'];

    $qb = $this->dbConn->createQueryBuilder();

    $qb->addSelect([
      'game.id             AS id',
      'game.number         AS number',
      'game.date           AS date',
      'game.time           AS time',
      'game.length         AS length',
      'game.timeslotlength AS length_slot',
      'game.status         AS status',
      'game.project_id     AS project_id',
      'game.short_note     AS notes',
      'game.published      AS published',
      'location.name       AS location_name',
      'level.name          AS level_name',
      'region.name         AS region_name',
    ]);

    $qb->from('Game','game');

    $qb->leftJoin('game','Project', 'project', 'project.id  = game.project_id');
    $qb->leftJoin('game','Location','location','location.id = game.location_id');
    $qb->leftJoin('game','AgeGroup','level',   'level.id    = game.agegroup_id');
    $qb->leftJoin('game','Region',  'region',  'region.id   = game.region_id');

    //$qb->leftJoin('game','team','homeTeam', 'homeTeam.id  = game.team1_id');
    //$qb->leftJoin('game','team','awayTeam', 'awayTeam.id  = game.team2_id');

    $qb->andWhere('game.project_id = :project_id');
    $qb->setParameter('project_id',$projectId);

    $qb->addOrderBy(('game.date'));
    $qb->addOrderBy(('level_name'));
    $qb->addOrderBy(('region_name'));
    $qb->addOrderBy(('location_name'));
    $qb->addOrderBy(('time'));

    $stmt = $qb->execute();
    $projectGames = [];
    $statusValues = ProjectGame::getStatusValues();
    while($projectGame = $stmt->fetch()) {
      $projectGame['state'] = $statusValues[$projectGame['status']];
      $projectGames[$projectGame['id']] = $projectGame;
    }

    /* ====================================================
     * Link the teams
     */
    $qb = $this->dbConn->createQueryBuilder();

    $qb->addSelect([
      'project_game_team.id      AS project_game_team_id',
      'project_game_team.slot    AS project_game_team_slot',
      'project_game_team.score   AS project_game_team_score',
      'project_game_team.source  AS project_game_team_source',

      'project_game_team.project_game_id  AS project_game_id',
      'project_game_team.project_team_id  AS project_team_id',

      'project_team.name AS project_team_name',
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
    while($projectGameTeam = $stmt->fetch()) {
      $projectGames[$projectGameTeam['project_game_id']]['teams'][$projectGameTeam['project_game_team_slot']] = $projectGameTeam;
    }
    // Done
    return $projectGames;
  }
  public function loadProjectTeams($criteria)
  {
    $projectId = $criteria['project'];

    $qb = $this->dbConn->createQueryBuilder();

    $qb->addSelect([
      'team.id          AS id',
      'team.name        AS name',
      'team.colors_home AS colors_home',
      'team.colors_away AS colors_away',
      'team.coach_name  AS coach_name',
      'team.coach_email AS coach_email',
      'team.coach_phone AS coach_phone',
      'team.poc_email   AS manager_email',
      'team.project_id  AS project_id',
      'level.name       AS level_name',
      'region.name      AS region_name',
    ]);

    $qb->from('Team','team');

    $qb->leftJoin('team','AgeGroup','level',  'level.id  = team.agegroup_id');
    $qb->leftJoin('team','Region',  'region', 'region.id = team.region_id');

    $qb->andWhere('team.project_id = :project_id');
    $qb->setParameter('project_id',$projectId);

    $qb->addOrderBy(('region_name'));
    $qb->addOrderBy(('level_name'));
    $qb->addOrderBy(('name'));

    $stmt = $qb->execute();
    $projectTeams = [];
    while($projectTeam = $stmt->fetch()) {
      $projectTeams[$projectTeam['id']] = $projectTeam;
    }
    return $projectTeams;
  }
  public function loadProjectLocations($criteria)
  {
    $projectId = $criteria['project'];

    $qb = $this->dbConn->createQueryBuilder();

    // Gives me a distinct list
    $qb->addSelect([
      'game.project_id      AS project_id',
      'location.id          AS id',
      'location.name        AS name',
      'location.long_name   AS name_long',
      'location.url         AS url',
      'location.poc_name    AS poc_name',
      'location.poc_phone1  AS poc_phone',
      'location.poc_email1  AS poc_email',
      'location.street1     AS street1',
      'location.street2     AS street2',
      'location.city        AS city',
      'location.zip         AS zip',
      'location.state       AS state',
      'location.latitude    AS latitude',
      'location.longitude   AS longitude',
    ]);

    $qb->from('Game','game');

    $qb->leftJoin('game','Location','location', 'location.id  = game.location_id');

    $qb->andWhere('game.project_id = :project_id');
    $qb->setParameter('project_id',$projectId);

    $qb->addOrderBy(('name'));

    $stmt = $qb->execute();
    $projectLocations = [];
    while($projectLocation = $stmt->fetch()) {
      $projectLocations[$projectLocation['id']] = $projectLocation;
    }
    return $projectLocations;
  }
  public function loadProjectRegions($criteria)
  {
    $projectId = $criteria['project'];

    $qb = $this->dbConn->createQueryBuilder();

    // Gives me a distinct list
    $qb->addSelect([
      'team.project_id        AS project_id',
      'region.id              AS id',
      'region.name            AS name',
      'region.long_name       AS name_long',
      'region.poc_name        AS poc_name',
      'region.poc_email       AS poc_email',
      'region.ref_admin_name  AS ref_admin_name',
      'region.ref_admin_email AS ref_admin_email',
    ]);

    $qb->from('Team','team');

    $qb->leftJoin('team','Region','region', 'region.id  = team.region_id');

    $qb->andWhere('team.project_id = :project_id');
    $qb->setParameter('project_id',$projectId);

    $qb->addOrderBy(('name'));

    $stmt = $qb->execute();
    $projectRegions = [];
    while($projectRegion = $stmt->fetch()) {
      $projectRegions[$projectRegion['id']] = $projectRegion;
    }
    return $projectRegions;
  }
  public function loadProjectAgeGroups($criteria)
  {
    $projectId = $criteria['project'];

    $qb = $this->dbConn->createQueryBuilder();

    // Gives me a distinct list
    $qb->addSelect([
      'level.project_id AS project_id',
      'level.id         AS id',
      'level.name       AS name',
      'level.difficulty AS difficulty',
      'region.name      AS region_name',

      'points_multiplier AS pm',
      'points_ref1       AS pr1',
      'points_youth_ref1 AS pr1y',
      'points_ref2       AS pr2',
      'points_youth_ref2 AS pr2y',
      'points_ref3       AS pr3',
      'points_youth_ref3 AS pr3y',
      'points_team_goal  AS ptg',
    ]);

    $qb->from('AgeGroup','level');

    $qb->leftJoin('level','Region','region', 'region.id  = level.region_id');

    $qb->andWhere('level.project_id = :project_id');
    $qb->setParameter('project_id',$projectId);

    $qb->addOrderBy(('region_name'));
    $qb->addOrderBy(('name'));

    $stmt = $qb->execute();
    $projectLevels = [];
    while($projectLevel = $stmt->fetch()) {
      $projectLevels[$projectLevel['id']] = $projectLevel;
    }
    return $projectLevels;
  }
  public function loadProjectOfficialPositions($criteria)
  {
    $projectId = $criteria['project'];

    $qb = $this->dbConn->createQueryBuilder();

    $qb->addSelect([
      'project.id   AS project_id',
      'project.name AS project_name',

      'official_position.id          AS id',
      'official_position.name        AS name',
      'official_position.shortname   AS name_short',
      'official_position.diffavail   AS diff_avail',
      'official_position.diffvisable AS diff_visible',
      'official_position.diffreq     AS diff_required',
    ]);

    $qb->from('project_offpos','project_official_position');

    $qb->leftJoin(
      'project_official_position',
      'OffPos',
      'official_position',
      'official_position.id = project_official_position.offpos_id'
    );
    $qb->leftJoin(
      'project_official_position',
      'Project',
      'project',
      'project.id = project_official_position.project_id'
    );
    $qb->andWhere('project.id = :project_id');

    $qb->setParameter('project_id',$projectId);
    $qb->addOrderBy(('id'));

    $stmt = $qb->execute();
    $positions = [];
    while($position = $stmt->fetch()) {
      $positions[$position['id']] = $position;
    }
    return $positions;
  }
  public function loadProjects(/** @noinspection PhpUnusedParameterInspection */
    $criteria)
  {
    $qb = $this->dbConn->createQueryBuilder();

    // Gives me a distinct list
    $qb->addSelect([
      'project.id         AS id',
      'project.name       AS name',
      'project.long_name  AS name_long',
      'project.start_date AS date_beg',
      'project.end_date   AS date_end',
      'project.sportstr   AS sport',

      'project.use_team_refpnt_rules  AS trr',
      'project.show_referee_region    AS srr',
    ]);

    $qb->from('Project','project');

    $qb->addOrderBy(('name'));

    $stmt = $qb->execute();
    $projects = [];
    while($project = $stmt->fetch()) {
      $projects[$project['id']] = $project;
    }
    return $projects;
  }
  public function loadProjectLevels($criteria)
  {
    $projectId = $criteria['project'];

    $qb = $this->dbConn->createQueryBuilder();

    // Gives me a distinct list
    $qb->addSelect([
      'level.id         AS id',
      'level.project_id AS project_id',
      'level.name       AS name',
      'level.title      AS title',
      'level.age        AS age',
      'level.gender     AS gender',
      'level.division   AS division',

      'level.game_slot_length AS game_slot_length',
      'level.crew_type        AS crew_type',
      'level.level_key        AS level_key'
    ]);

    $qb->from('project_levels','level');

    $qb->andWhere('project_id = :project_id');
    $qb->setParameter('project_id',$projectId);

    $qb->addOrderBy(('name'));

    $stmt = $qb->execute();
    $levels = [];
    while($level = $stmt->fetch()) {
      $levels[$level['id']] = $level;
    }
    return $levels;
  }
}
?>
