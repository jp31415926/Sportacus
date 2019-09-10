<?php
namespace Cerad\Bundle\ProjectBundle\EntityRepository;

use Scheduler\SchBundle\Entity\GameRepository;
use Scheduler\SchBundle\Entity\GameListCriteria;

class ProjectGameRepository extends GameRepository
{
  /* =========================================================
   * Link in sql based routines as needed
   *
   */
  /** @var  ProjectGameRepositorySql */
  protected $projectGameRepositorySql;

  protected function getProjectGameRepositorySql()
  {
    if (!$this->projectGameRepositorySql) {
      $this->projectGameRepositorySql = new ProjectGameRepositorySql($this->getEntityManager()->getConnection());
    }
    return $this->projectGameRepositorySql;
  }
  /* =========================================================
   * query for a distinct list of game ids that match the criteria
   */
  public function findGameIdsByCriteria($criteria = [])
  {
    $projectGameRepositorySql = $this->getProjectGameRepositorySql();

    return $projectGameRepositorySql->findGameIdsByCriteria($criteria);
  }
  /* =========================================================================
   * Basic query builder with plenth of joins
   * Just add where conditions and go
   */
  public function createQueryBuilderForGames()
  {
    $qb = $this->createQueryBuilder('game'); // project_game?

    $qb->addSelect('game, game_location, game_age_group, game_region');
    $qb->addSelect('project, project_official_positions');

    $qb->addSelect('project_game_teams, project_team, project_team_region');

    // Make this optional
    $qb->addSelect('project_game_officials, project_official, project_official_region');

    $qb->leftJoin('game.project', 'project');
    $qb->leftJoin('project.offpositions','project_official_positions'); // Need this?

    $qb->leftJoin('game.region',  'game_region');
    $qb->leftJoin('game.location','game_location');
    $qb->leftJoin('game.agegroup','game_age_group');

    $qb->leftJoin('game.projectGameTeams',         'project_game_teams');
    $qb->leftJoin('project_game_teams.projectTeam','project_team');
    $qb->leftJoin('project_team.region',           'project_team_region');

    $qb->leftJoin('game.projectGameOfficials',             'project_game_officials');
    $qb->leftJoin('project_game_officials.projectOfficial','project_official');
    $qb->leftJoin('project_official.region',               'project_official_region');

    return $qb;
  }
  /* ==============================================================
   * Standard load one game for id
   *
   */
  public function find($id, $lockMode = NULL, $lockVersion = NULL) //jp added new arguments to fix errors
  {
    if (!$id) return null;

    $qb = $this->createQueryBuilderForGames();
    $qb->where('game.id = :id');
    $qb->setParameter('id',$id);
    return $qb->getQuery()->getSingleResult();
  }
  /* ==========================================================
   * Main game query
   *
   */
  public function findGamesByCriteria(array $criteria)
  {
    $gameIds = isset($criteria['game_ids']) ?
      $criteria['game_ids'] :
      $this->findGameIdsByCriteria($criteria); // print_r(count($gameIds)); die();
    if (count($gameIds) < 1) return [];

    $qb = $this->createQueryBuilderForGames();

    $qb->where('game.id IN(:game_ids)');
    $qb->setParameter('game_ids',$gameIds);

    $qb->addOrderBy('game.date','ASC');
    $qb->addOrderBy('game.time','ASC');
    $qb->addOrderBy('game_location.name','ASC');

    return $qb->getQuery()->getResult();
  }
  /* =========================================================
   * Supports legacy query object
   */
  public function findByCriteria($onlyPublished,GameListCriteria $criteria)
  {
    if (!is_array($criteria))
    {
      $criteria = [
        'date_start' => $criteria->getStartDate()->format('Y-m-d'),
        'date_end'   => $criteria->getEndDate()  ->format('Y-m-d'),

        'team'     => $criteria->getTeam(),
        'coach'    => $criteria->getCoach(),
        'location' => $criteria->getLocation(),
        'official' => $criteria->getOfficial(),

        'only_published' => $onlyPublished,
      ];
    }
    return $this->findGamesByCriteria($criteria);
  }

}
