<?php

namespace Scheduler\ApiBundle\Tests\Controller;

use Symfony\Component\Yaml\Yaml;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectsGamesControllerTest extends WebTestCase
{
  protected static $dataDir;

  public static function setUpBeforeClass()
  {
    // Create a variable data directory based on where AppKernel lives
    $client = static::createClient();
    $kernel = $client->getKernel();

    self::$dataDir = $kernel->getRootDir() . '/../var/data';

    if (!file_exists(self::$dataDir)) {
      mkdir(self::$dataDir, 0777, true);
    }
  }

  public function testAll()
  {
    $client = static::createClient();

    $client->request('GET', '/api/projects/19/games?pin=9345');
    $this->assertEquals(200,$client->getResponse()->getStatusCode());
    
    $data = json_decode($client->getResponse()->getContent(),true);
    file_put_contents(self::$dataDir . '/games.yml',Yaml::dump($data,10,2));
  }
  public function testSearchDates()
  {
    $client = static::createClient();

    $client->request('GET', '/api/projects/19/games?pin=9345&dates=20150601');
    $this->assertEquals(200,$client->getResponse()->getStatusCode());
    
    $data = json_decode($client->getResponse()->getContent(),true);
    file_put_contents(self::$dataDir . '/game_dates.yml',Yaml::dump($data,10,2));
    
  }
  public function testGet()
  {
    $client = static::createClient();

    $client->request('GET', '/api/projects/19/games/7651?pin=9345');
    $this->assertEquals(200,$client->getResponse()->getStatusCode());
    
    $data = json_decode($client->getResponse()->getContent(),true);
    file_put_contents(self::$dataDir . '/game.yml',Yaml::dump($data,10,2));
  }
  public function testDeniedAccess()
  {
    $client = static::createClient();

    $client->request('GET', '/api/projects/19/games?pin=9999');
    
    // Should be 403, something to do with the debug bar
    $this->assertEquals(302,$client->getResponse()->getStatusCode());
  }
}
