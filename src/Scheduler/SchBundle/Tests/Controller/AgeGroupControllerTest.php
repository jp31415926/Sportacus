<?php

namespace Scheduler\SchBundle\Tests\Controller;

use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Scheduler\SchBundle\Entity\User;

class AgeGroupControllerTest extends AbstractControllerTestCase
{
  public function testIndex()
  {
    $client = static::createClient();
    $client->enableProfiler();
    $this->login($client);

    $crawler = $client->request('GET', '/agegroup/');
    //$profile = $client->getProfile();
    //$collectors = $profile->getCollectors();print_r($collectors); die();
    //$logger = $profile->getCollector('logger');
    //print_r($logger);
    //echo $client->getResponse()->getContent();
    $this->assertTrue($crawler->filter('html:contains("Age Groups")')->count() > 0);
  }
}