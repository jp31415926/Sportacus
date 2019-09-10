<?php
namespace Cerad\Bundle\ProjectBundle\Tests\EntityRepository;

use Symfony\Component\Yaml\Yaml;
use /** @noinspection PhpInternalEntityUsedInspection */
  Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

use Cerad\Bundle\ProjectBundle\EntityRepository\ProjectGameRepositorySql;

class ProjectGameRepositorySqlTest extends \PHPUnit_Framework_TestCase
{
  static $repo;
  static $dbConn;

  public static function setUpBeforeClass()
  {
    self::$dbConn = 'connected';
    $params = Yaml::parse(file_get_contents(__DIR__ . '/../../../../app/config/parameters.yml'));
    $params = $params['parameters'];

    /** @noinspection PhpInternalEntityUsedInspection */
    $config = new Configuration();
    $connParams = [
      'dbname'   => $params['database_name_sportacus'],
      'user'     => $params['database_user'],
      'password' => $params['database_password'],
      'host'     => $params['database_host'],
      'driver'   => $params['database_driver'],
      'driverOptions' => [\PDO::ATTR_EMULATE_PREPARES => false],
    ];
    self::$dbConn = DriverManager::getConnection($connParams, $config);
    self::$repo = new ProjectGameRepositorySql(self::$dbConn);

  }
  public function testFindOne()
  {
    $repo = self::$repo;

    $projectGame = $repo->findOne(7878);

    $this->assertEquals( 103, $projectGame['number']);

    $this->assertEquals('JH2',$projectGame['location']['name']);

    $projectTeamHome = $projectGame['project_game_teams']['home']['project_team'];

    $this->assertEquals('R0160-SU19B-02-Phonthibsvads (Mavericks)',$projectTeamHome['name']);

    $projectOfficials = $projectGame['project_game_officials'];
    $this->assertEquals( 3, count($projectOfficials));

    $referee = $projectOfficials['ref'];
    $this->assertEquals('Les', $referee['project_official']['name_first']);
  }
}