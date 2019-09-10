<?php

namespace Scheduler\SchBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PageControllerTest extends WebTestCase
{
  public function testIndex()
  {
    $client = static::createClient();

    $crawler = $client->request('GET', '/');

    $headers = $crawler->filter('h1');
    $this->assertEquals('Welcome!', $headers->first()->text());
    $this->assertEquals('About us', $headers->eq(1)->text());
  }

  public function testContact()
  {
    $client = static::createClient();

    $crawler = $client->request('GET', '/contact');

    $this->assertTrue($crawler->filter('html:contains("Contact Us")')->count() > 0);
  }

  public function testContactPost()
  {
    $client = static::createClient();

    $crawler = $client->request('GET', '/contact');

    $form = $crawler->selectButton('Submit')->form();

    $form['contact[name]'] = 'Lucas';
    $form['contact[email]'] = 'lucas@sportacus.com'; // example.com fails validation!
    $form['contact[subject]'] = 'Star Wars Official';
    $form['contact[body]'] = 'In a galaxy far away...';

    $client->submit($form);

    $this->assertTrue($client->getResponse()->isRedirect());

    $crawler = $client->followRedirect();

    $this->assertGreaterThan(0, $crawler->filter('html:contains("Your contact enquiry was successfully sent")')->count());
  }

  public function testHelp()
  {
    $client = static::createClient();

    $crawler = $client->request('GET', '/help');

    $this->assertTrue($crawler->filter('html:contains("Help topics:")')->count() > 0);
  }

  public function testHelpTopicLink()
  {
    $client = static::createClient();

    $crawler = $client->request('GET', '/help');

    $link = $crawler
      ->filter('a:contains("How to register for an account")')
      ->first()
      ->link() // Clicks the how to register link
    ;
    $crawler = $client->click($link);
    $this->assertTrue($crawler->filter('html:contains("How to Register")')->count() > 0);
  }

  public function testHelpTopic()
  {
    $client = static::createClient();

    $crawler = $client->request('GET', '/help/schedule');

    $this->assertTrue($crawler->filter('html:contains("Working with the Schedule")')->count() > 0);
  }
}
