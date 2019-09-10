<?php

namespace Scheduler\SchBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Scheduler\SchBundle\Entity\User;
use Scheduler\SchBundle\Entity\Game;

/**
 * Text Tropo interface controller.
 *
 * @Route("/text")
 */
class TextController extends Controller
{
  private $em;
  private $user;
  private $tokens;
  private $reply;

  private $commands = array(
    'score' => 'textScore',
    'status' => 'textStatus',
    'help' => 'textHelp',
    'quit' => 'textStop',
    'stop' => 'textStop',
    'send' => 'textSend',
    'accept' => 'textAccept',
    'decline' => 'textDecline',
  );

  /**
   * GAMES command
   *
   * Send games requested
   */
  public function textGames()
  {
    $games = $this->em->getRepository('SchedulerBundle:Game')->findAllActiveByOffcial($this->user->getId());
    $game = $games[0];
    $this->reply[] =
      "Game " . $game->getId()
      . ", " . $game->getDate()->format('M d')
      . ", " . $game->getTime()->format('g:ia')
      . ": " . $game->getTeam1()->getName()
      . " vs " . $game->getTeam2()->getName()
      . " @ " . $game->getLocation()->getName();
  }

  /**
   * SCORE command
   *
   * report score update
   *
   * Format: SCORE <game id> <home score> <away score> [<ref notes>]
   */
  public function textScore()
  {
    if (count($this->tokens) < 4) {
      $this->reply[] = 'SCORE command format invalid. Visit http://sportac.us/help/text for help.';
    } else {
      $gameid = strtolower($this->tokens[1]);
      $home = (int)$this->tokens[2];
      $away = (int)$this->tokens[3];
      $notes = '';
      // FIXME: get notes without changing
      if (count($this->tokens) > 4) {
        for ($x = 4; $x < count($this->tokens); ++$x) {
          $notes .= $this->tokens[$x] . ' ';
        }
        $notes = trim($notes);
      }
      $game = $this->em->getRepository('SchedulerBundle:Game')->find($gameid);

      if (!$game) {
        $this->reply[] = "Game with id of $gameid not found.";
      } else {
        if (($this->user->getRoleScheduler()) ||
          ($this->user == $game->getRef1()) ||
          ($this->user == $game->getRef2()) ||
          ($this->user == $game->getRef3())
        ) {
          if ($game->getStatus() == Game::STATUS_NORMAL)
            $game->setStatus(Game::STATUS_COMPLETE);
          $game->setScore1($home);
          $game->setScore2($away);
          if (!empty($notes)) {
            $n = $game->getRefNotes();
            if (!empty($n))
              $n .= "\n";
            $n .= "$notes - " . $this->user->getFullName() . "\n";
            $game->setRefNotes($n);
          }
          $this->em->persist($game);
          $this->em->flush();
          if (empty($notes)) {
            $this->reply[] = "Game $gameid score udpated to $home-$away (no notes)";
          } else {
            $this->reply[] = "Game $gameid score udpated to $home-$away: $notes";
          }

        } else {
          $this->reply[] = "Sorry, but you don't have permission to change Game $gameid.";
        }
      }
    }
  }

  /**
   * STATUS command
   *
   * update game status
   *
   * Format: STATUS <game id> <new status>
   */
  public function textStatus()
  {
    if (count($this->tokens) < 3) {
      $this->reply[] = 'STATUS command format invalid. Visit http://sportac.us/help/text for help.';
    } else {
      $gameid = strtolower($this->tokens[1]);
      $game = $this->em->getRepository('SchedulerBundle:Game')->find($gameid);
      if (!$game) {
        $this->reply[] = 'Game $gameid not found.';
      } else {
        if (($this->user->getRoleScheduler()) ||
          ($this->user == $game->getRef1()) ||
          ($this->user == $game->getRef2()) || // TODO: do we want ARs to be able to change game status?
          ($this->user == $game->getRef3())
        ) {
          $stat = $this->matchPartialValues($this->tokens[2], Game::getStatusValues());
          if ($stat !== FALSE) {
            $game->setStatus($stat);
            $this->em->persist($game);
            $this->em->flush();
            $this->reply[] = "Game $gameid status udpated to " . $game->getStatusString() . '.';
          } else {
            $this->reply[] = "The status value you provided was not recognized.";
          }
        } else {
          $this->reply[] = "You are not assigned to Game $gameid.";
        }
      }
    }
  }

  /**
   * HELP command
   *
   * help with commands
   */
  public function textHelp()
  {
    if (count($this->tokens) > 1) {
      $cmd = strtolower($this->tokens[1]);
      if ($cmd == 'score') {
        $this->reply[] = "SCORE <game#> <home score> <away score> [<notes>] - report score for game you are assigned to.";
      } else if ($cmd == 'games') {
        $this->reply[] = "GAMES [+/-days] - get list of games for today or -days before, +days after today.";
      } else if ($cmd == 'remind') {
        if (count($this->tokens) > 2) {
          $cmd = strtolower($this->tokens[2]);
          if ($cmd == 'game') {
            $this->reply[] = "REMIND GAME - get text reminders for games";
          } else if ($cmd == 'score') {
            $this->reply[] = "REMIND SCORE - get text reminders to complete scorecards";
          } else {
            $this->reply[] = "REMIND <type>=GAME or SCORE - Text HELP REMIND GAME or SCORE for help.";
          }
        } else {
          $this->reply[] = "REMIND <type>=GAME or SCORE - Text HELP REMIND GAME or SCORE for help.";
        }
      } else if (($cmd == 'accept') || ($cmd == 'acc')) {
        $this->reply[] = "ACC [<game id>] - accept an assignment, no id needed if only one game pending";
      } else if (($cmd == 'decline') || ($cmd == 'dec')) {
        $this->reply[] = "DEC [<game id>] [<reason>] - decline an assignment, no id needed if only one game pending";
      } else if ($cmd == 'quit') {
        $this->reply[] = "QUIT <type> - type is GAME or SCORE - stop reminder texts.";
      } else {
        $this->reply[] = "I don't know the command '$cmd'. Try again.";
      }
    } else {
      $this->reply[] = 'Visit http://sportac.us/help/text for help.';
    }
  }

  /**
   * Stop command
   *
   * Stop various text nofification messages
   */
  public function textStop()
  {
    $options = array(
      'all',
      'change',
      'reminder',
      'assignment',
    );
    $flush = FALSE;

    if (count($this->tokens) > 1) {
      $opt = $this->matchPartialValue($this->tokens[1], $options);
      if ($opt !== FALSE) {
        $cmd = $options[$opt];
        if ($cmd == 'all') {
          $this->reply[] = "As requested, Sportac.us will stop sending change, reminder and assignment text messages to you.";
          $this->user->setOptionChangeText(FALSE);
          $this->user->setOptionReminderText(FALSE);
          $this->user->setOptionAssignmentText(FALSE);
          $flush = TRUE;
        } else if ($cmd == 'change') {
          $this->reply[] = "As requested, Sportac.us will stop sending change text messages to you.";
          $this->user->setOptionChangeText(FALSE);
          $flush = TRUE;
        } else if ($cmd == 'reminder') {
          $this->reply[] = "As requested, Sportac.us will stop sending reminder text messages to you.";
          $this->user->setOptionReminderText(FALSE);
          $flush = TRUE;
        } else if ($cmd == 'assignment') {
          $this->reply[] = "As requested, Sportac.us will stop sending assignment text messages to you.";
          $this->user->setOptionAssignmentText(FALSE);
          $flush = TRUE;
        }
      }
    }
    if ($flush) {
      $this->em->persist($this->user);
      $this->em->flush();
    } else {
      $this->reply[] = 'Text QUIT ALL, CHANGE, REMIND, or ASSIGN, or visit http://sportac.us/help/text for help.';
    }
  }

  /**
   * Send command
   *
   * Start sending various text nofification messages
   */
  public function textSend()
  {
    $options = array(
      'all',
      'change',
      'reminder',
      'assignment',
    );
    $flush = FALSE;

    if (count($this->tokens) > 1) {
      $opt = $this->matchPartialValue($this->tokens[1], $options);
      if ($opt !== FALSE) {
        $cmd = $options[$opt];
        if ($cmd == 'all') {
          $this->reply[] = "As requested, Sportac.us will start sending change, reminder and assignment text messages to you.";
          $this->user->setOptionChangeText(TRUE);
          $this->user->setOptionReminderText(TRUE);
          $this->user->setOptionAssignmentText(TRUE);
          $flush = TRUE;
        } else if ($cmd == 'change') {
          $this->reply[] = "As requested, Sportac.us will start sending change text messages to you.";
          $this->user->setOptionChangeText(TRUE);
          $flush = TRUE;
        } else if ($cmd == 'reminder') {
          $this->reply[] = "As requested, Sportac.us will start sending reminder text messages to you.";
          $this->user->setOptionReminderText(TRUE);
          $flush = TRUE;
        } else if ($cmd == 'assignment') {
          $this->reply[] = "As requested, Sportac.us will start sending assignment text messages to you.";
          $this->user->setOptionAssignmentText(TRUE);
          $flush = TRUE;
        }
      }
    }
    if ($flush) {
      $this->em->persist($this->user);
      $this->em->flush();
    } else {
      $this->reply[] = 'Text SEND ALL, CHANGE, REMIND, or ASSIGN, or visit http://sportac.us/help/text for help.';
    }
  }

  /**
   * ACCEPT command
   *
   * Accept an assignment
   */
  public function textAccept()
  {
    $this->reply[] = "This command is not implemented yet. Sorry.";
  }

  /**
   * DECLINE command
   *
   * Decline an assignment
   */
  public function textDecline()
  {
    $this->reply[] = "This command is not implemented yet. Sorry.";
  }

  // TODO: this needs to move to a separate bundle or file
  public function sendEmailMessage($emailTo, $subject, $msg)
  {
    // send an email
    $message = \Swift_Message::newInstance()
      ->setSubject('[Sportac.us] ' . $subject)
      ->setFrom(array('notification@sportac.us' => 'Sportac.us Scheduling System'))
      ->setTo($emailTo)
      ->setBody($msg);
    $this->get('mailer')->send($message);
  }

  /**
   * Find user by mobile phone number and act on text commands
   *
   * @Route("/{phone}/{text}", name="text_cmd")
   */
  public function textAction($phone, $text)
  {
    // delete all characters in callerid except digits, then cut down to 10 digits
    // FIXME: this won't work for international numbers, but tropo can't text int numbers anyway, so good for now.
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) > 10) {
      $phone = substr($phone, strlen($phone) - 10);
    }
    $text = urldecode($text);

    $this->em = $this->getDoctrine()->getManager();
    $this->reply = array();
    //$this->reply[] = "Phone: $phone; $text";

    $this->user = $this->em->getRepository('SchedulerBundle:User')->loadUserByMobilePhone($phone);
    if ($this->user === FALSE) {
      $this->reply[] = "I don't recognize your number. Go to http://sportac.us to register for an account or get help.";
      $name = 'unknown user';
    } else {
      $this->tokens = $this->tokinizeString(trim($text));
      $func = $this->matchPartialName($this->tokens[0], $this->commands);
      if ($func) {
        call_user_func(array($this, $func));
      } else {
        // help
        //$this->reply[] = 'Sportac.us: text HELP <command> for more help: SCORE, GAMES, REMIND, ACC, DEC, QUIT';
        $this->reply[] = 'Sportac.us commands: SCORE, STATUS, STOP/QUIT, SEND, ACCEPT, DECLINE. Visit http://sportac.us/help/text for help';
      }
      $name = $this->user->getFullName();
    }
    $json = json_encode($this->reply);
    $msg = "Text from: $phone ($name)\r\nText: $text\r\nReply: $json\r\n";
    $this->sendEmailMessage(array('jp_text@gcfl.net' => 'John Price'), "Text from $name", $msg);

    $response = new \Symfony\Component\HttpFoundation\Response($json);
    $response->headers->set('Content-Type', 'application/json');
    return $response;
  }

  public function matchPartialName($cmd, &$cmdlist)
  {
    $cmd = strtolower($cmd);
    $cnt = 0;
    foreach ($cmdlist as $n => $v) {
      if (strncmp($cmd, $n, strlen($cmd)) == 0) {
        ++$cnt;
        $result = $v;
      }
    }
    if ($cnt == 1)
      return $result;
    return FALSE;
  }

  public function matchPartialValue($cmd, &$cmdlist)
  {
    $cmd = strtolower($cmd);
    $cnt = 0;
    foreach ($cmdlist as $n => $v) {
      if (strncmp($cmd, $v, strlen($cmd)) == 0) {
        ++$cnt;
        $result = $n;
      }
    }
    if ($cnt == 1)
      return $result;
    return FALSE;
  }

  public function tokinizeString($s)
  {
    $delimiters = ' ,./:;()-|';
    $result = array();
    $tok = strtok($s, $delimiters);
    while ($tok !== false) {
      $result[] = $tok;
      $tok = strtok($delimiters);
    }
    return $result;
  }

}
