<?php
namespace Cerad\Bundle\ProjectBundle\Tests\EntityRepository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Scheduler\SchBundle\Entity\GameListCriteria;

class ProjectGameRepositoryTestReference extends WebTestCase
{
  protected function getProjectGameRepository()
  {
    $client = static::createClient();
    $dic = $client->getContainer();
    return $dic->get('sportacus_project_game_repository_doctrine');
  }
  public function testFindById()
  {
    $game = $this->getProjectGameRepository()->find(7878);
    $gameOfficials = $game->getProjectGameOfficials();
    $this->assertEquals(3,count($gameOfficials));
  }
  /* ==========================================
   * Want to swap out main findByCriteria method
   */
  public function testFindByCriteriaDates()
  {
    $criteria2 = [
      'date_start' => '20150620',
      'date_end'   => '20150621',
    ];
    $gameIds = $this->getProjectGameRepository()->findGameIdsByCriteria($criteria2);
    $this->assertEquals(44,count($gameIds));

    $criteria = new GameListCriteria();
    $criteria->setStartDate(new \DateTime('2015-06-20'));
    $criteria->setEndDate  (new \DateTime('2015-06-21'));

    $games = $this->getProjectGameRepository()->findByCriteria(false,$criteria);
    $this->assertEquals(44,count($games));

    $game = $games[20];
    //$this->assertEquals('R0124-SU12B-01-Smith (Elite)',$game->getTeam1()->getName());
    $this->assertEquals('R0124-SU12B-01-Smith (Elite)',$game->getProjectGameTeamHome()->projectTeam->getName());
  }
  public function testFindByCriteriaDatesLocation()
  {
    $criteria = new GameListCriteria();
    $criteria->setStartDate(new \DateTime('2015-06-20'));
    $criteria->setEndDate  (new \DateTime('2015-06-21'));
    $criteria->setLocation('JH');

    $games = $this->getProjectGameRepository()->findByCriteria(false,$criteria);
    $this->assertEquals(11,count($games));
  }
  public function testFindByCriteriaDatesTeamName()
  {
    $criteria = new GameListCriteria();
    $criteria->setStartDate(new \DateTime('2015-06-20'));
    $criteria->setEndDate  (new \DateTime('2015-06-21'));
    $criteria->setTeam('R0551-SU19G');

    $games = $this->getProjectGameRepository()->findByCriteria(false,$criteria);
    $this->assertEquals(2,count($games));
  }
  public function testFindByCriteriaDatesTeamCoach()
  {
    $criteria = new GameListCriteria();
    $criteria->setStartDate(new \DateTime('2015-06-20'));
    $criteria->setEndDate  (new \DateTime('2015-06-21'));
    $criteria->setCoach('Dan');

    $games = $this->getProjectGameRepository()->findByCriteria(false,$criteria);
    $this->assertEquals(2,count($games));
  }
  public function testFindByCriteriaDatesOfficialFirstName()
  {
    $criteria = new GameListCriteria();
    $criteria->setStartDate(new \DateTime('2015-06-20'));
    $criteria->setEndDate  (new \DateTime('2015-06-21'));
    $criteria->setOfficial('Sean');

    $games = $this->getProjectGameRepository()->findByCriteria(false,$criteria);
    $this->assertEquals(4,count($games));
  }
  public function testFindByCriteriaDatesOfficialLastName()
  {
    $criteria = new GameListCriteria();
    $criteria->setStartDate(new \DateTime('2015-06-20'));
    $criteria->setEndDate  (new \DateTime('2015-06-21'));
    $criteria->setOfficial('Cusker');

    $games = $this->getProjectGameRepository()->findByCriteria(false,$criteria);
    $this->assertEquals(1,count($games));

    $game = $games[0];
    $gameOfficials = $game->getProjectGameOfficials();
    $this->assertEquals(3,count($gameOfficials));

  }
  /* ==========================================
   * This is fragile
   * Need to add a reference test database
   */
  public function testFindAllWithTeams()
  {
    $client = static::createClient();
    $dic = $client->getContainer();
    $projectGameRepository = $dic->get('sportacus_project_game_repository_doctrine');

    $games = $projectGameRepository->findAllForProjectsWithTeams([19]);
    $this->assertEquals(143,count($games));

    foreach($games as $game)
    {
      $homeTeam = $game->getTeam1();
      $this->assertEquals('R0160-SU14B-01-Freeman (International FC)',$homeTeam->getName());
      return;
    }
  }
  public function testFindAllWithProjectTeams()
  {
    $client = static::createClient();
    $dic = $client->getContainer();
    $projectGameRepository = $dic->get('sportacus_project_game_repository_doctrine');

    $games = $projectGameRepository->findAllForProjectsWithProjectTeams([19]);
    $this->assertEquals(143,count($games));

    foreach($games as $game)
    {
      $projectGameTeams = $game->getProjectGameTeams();
      $this->assertCount(2,$projectGameTeams);

      $projectGameTeamAway = $game->getProjectGameTeamAway();
      $projectTeamAway     = $projectGameTeamAway->projectTeam;

      $this->assertEquals('R1011-SU14B-01-Glasgow (MC Hammers)',$projectTeamAway->getName());

      $this->assertEquals('R0160-SU14B-01-Freeman (International FC)',
        $game->getProjectGameTeamHome()->projectTeam->getName());

      return;
    }
  }
}
