<?php

namespace Scheduler\SchBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Scheduler\SchBundle\Entity\User;

/** Some base test functionality */
class AbstractControllerTestCase extends WebTestCase
{
  protected function login($client, $userId = 36)
  {
    /* The fos user provider always refreshes the user on each request
     * So we need to pass a valid id which has no public setters
     */
    $user  = new User();
    $userClass = new \ReflectionClass($user);
    $userIdProp = $userClass->getProperty('id');
    $userIdProp->setAccessible(true);
    $userIdProp->setValue($user,$userId);

    $session = $client->getContainer()->get('session');

    $firewall = 'main';
    $token = new UsernamePasswordToken($user, null, $firewall); //, ['ROLE_ADMIN']);
    $session->set('_security_'.$firewall, serialize($token));
    $session->save();

    $cookie = new Cookie($session->getName(), $session->getId());
    $client->getCookieJar()->set($cookie);
  }
}