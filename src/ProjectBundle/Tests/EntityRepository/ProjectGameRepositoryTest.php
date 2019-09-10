<?php
namespace Cerad\Bundle\ProjectBundle\Tests\EntityRepository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectGameRepositoryTest extends WebTestCase
{
  protected function getProjectGameRepository()
  {
    $client = static::createClient();
    $dic = $client->getContainer();
    return $dic->get('sportacus_project_game_repository_doctrine');
  }
  public function testRepositoryClass()
  {
    $repo = $this->getProjectGameRepository();
    $this->assertEquals('Cerad\Bundle\ProjectBundle\EntityRepository\ProjectGameRepository',get_class($repo));
  }
  public function testProjectGamesJoin()
  {
    $repo = $this->getProjectGameRepository();
    $qb = $repo->createQueryBuilder('game');
    $qb->addSelect('game','project_game_team');
    $qb->leftJoin('game.projectGameTeams','project_game_team');
    $qb->where('game.id = :id');
    $qb->setParameter('id',7878);

    $projectGame = $qb->getQuery()->getSingleResult();
    $this->assertEquals(7878,$projectGame->getId());

    $projectGameTeams = $projectGame->getProjectGameTeams();
    if (!count($projectGameTeams)) return;

    $this->assertEquals(2,count($projectGameTeams));

    $projectHomeTeam = $projectGame->getProjectGameTeamHome()->projectTeam;
    $this->assertEquals('R0160-SU19B-02-Phonthibsvads (Mavericks)',$projectHomeTeam->getName());
  }
}
