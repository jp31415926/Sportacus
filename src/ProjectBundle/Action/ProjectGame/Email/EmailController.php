<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Email;

use Psr\Http\Message\ServerRequestInterface as Request;

//  Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Cerad\Bundle\ProjectBundle\EntityRepository\ProjectGameRepositorySql;

use Cerad\Bundle\ProjectBundle\Action\ProjectGame\Show\ShowContentComponent;

class EmailController extends Controller
{
  protected $emailForm;
  protected $repository;

  public function __construct(
    ProjectGameRepositorySql $repository,
    EmailForm                $emailForm
  )
  {
    $this->emailForm  = $emailForm;
    $this->repository = $repository;
  }
  protected function getMailer()
  {
    return $this->get('mailer');
  }
  public function __invoke(Request $request, $gameId = 0)
  {
    // Only for admins
    if (!$this->isGranted('ROLE_ADMIN')) {
      throw new AccessDeniedException();
    }
    $flash = null;

    $game = $this->repository->findOne($gameId);

    $emailForm = $this->emailForm;

    $emailForm->setGame($game);

    $emailForm->handleRequest($request);

    if ($emailForm->isValid()) {

      $data = $emailForm->getData(); // print_r($data); die();

      /** @var \Swift_Mailer $mailer */
      $mailer = $this->getMailer();

      /** @var \Swift_Message $message */
      $message = $mailer->createMessage();

      // Needs to be refined
      $reply = $data['from'];
      $email = key($reply);

      $message->setFrom   ($email,$reply[$email]);
      $message->setReplyTo($email,$reply[$email]);

      $message->setTo($data['tos']);

      $message->setSubject($data['subject']);

      $message->setBody($data['body']);

      $mailer->send($message);
      $flash = 'Email Sent';
    }

    $tplData = [
      'emailForm' => $emailForm,
      'flash'     => $flash,
    ];
    return $this->render('@CeradProject/ProjectGame/Email/EmailTemplate.html.twig',$tplData);
  }
}