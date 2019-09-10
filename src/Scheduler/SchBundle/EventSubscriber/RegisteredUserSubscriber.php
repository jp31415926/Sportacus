<?php

namespace Scheduler\SchBundle\EventSubscriber;

//use FOS\UserBundle\FOSUserEvents;
//use FOS\UserBundle\Event\FormEvent;
//use FOS\UserBundle\Event\FilterUserResponseEvent;
//use FOS\UserBundle\Mailer\MailerInterface;
//use FOS\UserBundle\Util\TokenGeneratorInterface;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
//use Symfony\Component\HttpFoundation\RedirectResponse;
//use Symfony\Component\HttpFoundation\Session\SessionInterface;
//use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
//use Scheduler\SchBundle\Entity\User;

// use Scheduler\SchBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;

class RegisteredUserSubscriber implements EventSubscriberInterface
{
  private $mailer;
  //private $tokenGenerator;
  //private $router;
  //private $session;

  // TODO AH: Inject the container to avoid creating the mailer on every request
  public function __construct(\Swift_Mailer $mailer)
  {
    $this->mailer = $mailer;
  }

  public static function getSubscribedEvents()
  {
    return [
      FOSUserEvents::REGISTRATION_CONFIRMED => 'onRegistrationConfirmed',
      //'fos_user.registration.confirmed' => 'onRegistrationConfirmed',
    ];
  }

  public function onRegistrationConfirmed(FilterUserResponseEvent $event)
  {
    /** @var $user \FOS\UserBundle\Model\UserInterface */
    $user = $event->getUser();
    $name = $user->getFullName() . ' (' . $user->getUsername() . ')'; // die($name);
    $email = $user->getEmail();
    $region = $user->getRegion();
    $ayso = (int)$user->getAysoId();
    $cc = [];
    if ($region->getRefAdminEmail()) {
      $cc = [$region->getRefAdminEmail() => $region->getRefAdminName()];
    }
    // TODO: verify user and add them to referee group if in list

    // send an email
    $message = \Swift_Message::newInstance()
      ->setSubject("[Sportac.us] New user: $name - $ayso")
      ->setFrom(array('registration@sportac.us' => 'Sportac.us Scheduling System'))
      ->setCc(array('ara@ayso894.net' => 'Area Ref Admin'))// FIXME
      ->setTo($cc)
      ->setBody(
<<<EOT
User $name <$email> just successfully registered on Sportac.us.

AYSO number is $ayso. Region is $region.

Please verify they are a valid volunteer and have the proper training by doing the following.
A. Verify training
  1. Go to https://national.ayso.org/Volunteers/ViewCertification?UserName=$ayso
  2. Verify they are a Referee by going to the Referee tab and looking for "Regional Referee" or something similar. The following are not Referee certifications valid:
    - z-Online Regional Referee without Safe Haven
    - Z-Online AYSO Summary of the Laws of the Game
  3. Verify they have taken "Z-Online AYSOs Safe Haven" and "Z-Online CDC Concussion Awareness Training" once. (Safe Haven tab)

B. Verify a volunteer is eligible to volunteer as a referee on Blue Sombrero:
  1. Login to your region's website.
  2. Click Reports.
  3. Click 4. Volunteer Verification Status Report -> View
  4. Click the "Volunteer Last Name" header to sort by last name.
  5. Find the volunteer's name and verify their status is Green, Brown, Yellow or Orange.

(More info about the color codes: https://bluesombrero.zendesk.com/hc/en-us/articles/115001489712-AYSO-Volunteer-Status-Color-Key)

EOT
							);
// FIXME: read email body from this file, but "render" and "renderView" is not found.
//    ->setBody($this->render('SchedulerBundle:User:newUserEmail.txt.twig',
//        array('name' => $name, 'email' => $email, 'ayso' => $ayso, 'region' => $region)));
    $this->mailer->send($message);
  }
}
