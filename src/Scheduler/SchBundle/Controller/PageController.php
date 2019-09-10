<?php
// src/Scheduler/SchBundle/Controller/PageController.php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\Enquiry;
use Scheduler\SchBundle\Form\EnquiryType;
use ReCaptcha\ReCaptcha; // Include the recaptcha lib

class PageController extends Controller
{
  /**
   * Home page.
   *
   * @Route("/", name="scheduler_homepage")
   * @Template()
   */
  public function indexAction()
  {
    return $this->render('SchedulerBundle:Page:index.html.twig');
  }

  /**
   * Help page.
   *
   * @Route("/help", name="help")
   * @Template()
   */
  public function helpAction()
  {
    return $this->render("SchedulerBundle:Page:help.html.twig");
  }

  /**
   * Help sub-pages.
   *
   * @Route("/help/{topic}", name="help_topic")
   * @Template()
   */
  public function helpTopicAction($topic)
  {
    $topic = preg_replace('/[^0-9a-zA-Z_]/', '', $topic);
    return $this->render("SchedulerBundle:Page:help.$topic.html.twig");
  }

  /**
   * Contact page.
   *
   * @Route("/contact", name="scheduler_contact")
   * @Template()
   */
  public function contactAction(Request $request)
  {
    $enquiry = new Enquiry();
    $form = $this->createForm(EnquiryType::class, $enquiry);
    $form->handleRequest($request);
    $resp = '';
    if ($form->isValid()) {
      $recaptcha_secret = $this->container->getParameter('recaptcha_secret');
      $recaptcha = new ReCaptcha($recaptcha_secret);
      $resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());
    
      if (!$resp->isSuccess()) {
        // Do something if the submit wasn't valid ! Use the message to show something
        $errors = '';
        foreach ($resp->getErrorCodes() as $e) {
          if (!empty($errors)) {
            $errors .= ', ';
          }
          $errors .= $e;
        }
        $message = "The reCAPTCHA wasn't entered correctly. Go back and try it again. (reCAPTCHA said: " . $errors . ")";
        $this->get('session')->getFlashBag()->add('contact-error', $message);
      } else {
        // send an email
        $message = \Swift_Message::newInstance()
          ->setSubject('Contact enquiry from Sportac.us')
          ->setFrom($this->container->getParameter('scheduler.contact.emails.from'))
          ->setTo($this->container->getParameter('scheduler.contact.emails.to'))
          ->setBody($this->renderView('SchedulerBundle:Page:contactEmail.txt.twig', array('enquiry' => $enquiry)));
        $this->get('mailer')->send($message);

        $this->get('session')->getFlashBag()->add('contact-notice', 'Your contact enquiry was successfully sent. Thank you!');
        
        // Redirect - This is important to prevent users re-posting
        // the form if they refresh the page
        return $this->redirect($this->generateUrl('scheduler_contact'));
      }
    }
    return $this->render('SchedulerBundle:Page:contact.html.twig',
      array('form' => $form->createView())
    );
  }
}

