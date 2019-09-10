<?php

namespace Scheduler\SchBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GameControllerTest extends WebTestCase
{
  public function testGameIndex()
  {
    $client = static::createClient();

    $crawler = $client->request('GET', '/game/');

    $this->assertTrue($crawler->filter('html:contains("Game Schedule")')->count() > 0);
  }

  public function testGameSearch()
  {
    $client = static::createClient();

    $crawler = $client->request('GET', '/game/');

    $form = $crawler->selectButton('Search')->form();
    $formName = 'scheduler_schbundle_gamelistcriteriatype';
    $form[$formName . '[start_date]'] = '2015-06-20';
    $form[$formName . '[end_date]'] = '2015-06-21';
    $form[$formName . '[location]'] = 'JH';

    $client->submit($form);

    $this->assertTrue($client->getResponse()->isRedirect());

    $crawler = $client->followRedirect();

    $this->assertGreaterThan(0, $crawler->filter('html:contains("11 Games")')->count());
  }

  public function testGameShow()
  {
    $client = static::createClient();

    $crawler = $client->request('GET', '/game/7829/show');

    $this->assertTrue($crawler->filter('html:contains("Show Game")')->count() > 0);
  }

  public function testGameEdit()
  {
    $client = static::createClient();

    $crawler = $client->request('GET', '/game/7829/edit');

    // TODO: Login
  }
  /*
  public function testCompleteScenario()
  {
      // Create a new client to browse the application
      $client = static::createClient();

      // Create a new entry in the database
      $crawler = $client->request('GET', '/game/');
      $this->assertTrue(200 === $client->getResponse()->getStatusCode());
      $crawler = $client->click($crawler->selectLink('Create a new entry')->link());

      // Fill in the form and submit it
      $form = $crawler->selectButton('Create')->form(array(
          'scheduler_schbundle_gametype[field_name]'  => 'Test',
          // ... other fields to fill
      ));

      $client->submit($form);
      $crawler = $client->followRedirect();

      // Check data in the show view
      $this->assertTrue($crawler->filter('td:contains("Test")')->count() > 0);

      // Edit the entity
      $crawler = $client->click($crawler->selectLink('Edit')->link());

      $form = $crawler->selectButton('Edit')->form(array(
          'scheduler_schbundle_gametype[field_name]'  => 'Foo',
          // ... other fields to fill
      ));

      $client->submit($form);
      $crawler = $client->followRedirect();

      // Check the element contains an attribute with value equals "Foo"
      $this->assertTrue($crawler->filter('[value="Foo"]')->count() > 0);

      // Delete the entity
      $client->submit($crawler->selectButton('Delete')->form());
      $crawler = $client->followRedirect();

      // Check the entity has been delete on the list
      $this->assertNotRegExp('/Foo/', $client->getResponse()->getContent());
  }

  */
}