<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Reminder;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\DBAL\Connection;
use Cerad\Bundle\ProjectBundle\EntityRepository\ProjectGameRepositorySql as Repository;
use Cerad\Bundle\ProjectBundle\Action\ProjectGame\Twig\TwigExtension as TwigExtension;

class GameReminderEmailCommand extends Command
{
  /** @var Connection  */
  protected $dbConn;
  protected $repository;

  protected $viewHelper;
  protected $mailer;

  public function __construct(Repository $repository, \Swift_Mailer $mailer, TwigExtension $viewHelper)
  {
    $this->mailer     = $mailer;
    $this->viewHelper = $viewHelper;
    $this->repository = $repository;
    $this->dbConn     = $repository->getDatabaseConnection();

    parent::__construct();
  }
  protected function configure()
  {
    $this->setName('cerad_project_game_reminder_email');
    $this->setDescription('Send Upcoming Game Email Reminders');

    $this->addOption('send',   null, InputOption::VALUE_REQUIRED, 'Actually Send the Messages',false);
    $this->addOption('project',null, InputOption::VALUE_REQUIRED, 'Project ID',null);
    $this->addOption('date',   null, InputOption::VALUE_REQUIRED, 'date yyyy-mm-dd',null);

    $help = <<<EOT
To send emails for upcoming games:
app/console cerad_project_game_reminder_email --env=prod --send=1 --project=20 --date=2015-08-18
All the parameters are optional.

--date    Defaults to tomorrow's date.  Explicitly setting it is useful for testing and possibly tournaments.
--project Defaults to all games for the specified date.
--send=1  Is required to actually send the emails.  --send=0 or just leaving it out can be used for testing.
--env
  Defaults to the development environment.
  In development, emails will be sent to the address specified by the mailer_delivery_address_dev parameter.
  Examining the headers will reveal to whom the email would have actually been sent.

  As currently implemented, one email will be sent per game contact per day.
  So if a referee has signed up for two games and they are coaching a third,
  they will receive one email with all three games listed.

  The legacy implementation sent one email per game to all the contacts for the game.
  This has the advantage giving each contact the email address of all the other contacts.
  But it often means multiple emails per contact if they had multiple games.
  It's easy enough to switch back to the legacy implementation.

  Note that the contact's option_reminder_email is not currently being checked.
EOT;
    $this->setHelp($help);
  }
  /* ===============================================================
   * Generate list of games based on criteris
   *
   */
  protected function findGameIds($criteria)
  {
    $qb = $this->dbConn->createQueryBuilder();
    $qb->addSelect('DISTINCT game.id AS id');
    $qb->from('Game','game');

    $qb->andWhere('game.status = 1');
    $qb->andWhere('game.published = 1');

    $qb->leftJoin('game','AgeGroup','level','level.id = game.agegroup_id');
    $qb->andWhere('level.difficulty > 5');

    $qb->andWhere('game.date = :date');
    $qb->setParameter('date',$criteria['date']);

    if (isset($criteria['project_id'])) {
      $qb->andWhere('project_game.project_id = :project_id');
      $qb->setParameter('project_id',$criteria['project_id']);
    }
    //echo $qb->getSQL();
    $stmt = $qb->execute();
    $ids = [];
    while($row = $stmt->fetch()) {
      $ids[] = $row['id'];
    }
    //print_r($ids);
    return $ids;
  }
  /* ===========================================================
   * Generate the email content for a sinfle game
   *
   */
  protected function renderEmailContentForGame($game)
  {
    $viewHelper = $this->viewHelper;

    $dt = \DateTime::createFromFormat('Y-m-d H:i:s',"{$game['date']} {$game['time']}");

    $field = $game['location'];

    $msg = <<<EOT
Game# {$game['number']}
Date: {$dt->format('l, F j, Y')}
Time: {$dt->format('g:i A')}
Length: {$game['length']} mins (should complete in {$game['timeslotlength']} mins)
Location:  {$field['long_name']} ({$field['name']}) http://sportac.us/location/redirect/{$field['id']}

EOT;

    foreach($viewHelper->projectGameTeamsSortedFilter($game) as $gameTeam) {

      $msg .= sprintf("\n%s:\nName: %s\nColors: %s\nCoach Name: %s\nCoach Email: %s\n",
        $viewHelper->projectGameTeamSlotFilter      ($gameTeam),
        $viewHelper->projectGameTeamNameFilter      ($gameTeam),
        $viewHelper->projectGameTeamColorsFilter    ($gameTeam,'Unknown Colors'),
        $viewHelper->projectGameTeamCoachNameFilter ($gameTeam),
        $viewHelper->projectGameTeamCoachEmailFilter($gameTeam)
      );
    }
    if ($viewHelper->projectGameTeamsColorsMatchFilter($game)) {
      $msg .=
        "\n*** Warning: Both teams have the same colors ***.\n" .
        "Home team is responsible for having pinnies available if the referee determines that team's colors are too similar.\n";
    }
    $msg .= "\nOfficials assigned to this game:\n";

    foreach($viewHelper->projectGameOfficialsSortedFilter($game) as $gameOfficial) {
      $slot = $viewHelper->projectGameOfficialSlotFilter($gameOfficial);
      $name = $viewHelper->projectGameOfficialNameFilter($gameOfficial);
      $email = $viewHelper->projectGameOfficialEmailFilter($gameOfficial);
      if (!empty($name)) {
        $msg .= "$slot: $name <$email>\n";
      }
    }
    $msg .= <<<EOT

Officials, please visit http://sportac.us/game/scorecard/{$game['id']} to enter an Official Report after the game.
Even if no score is kept, please change the status as appropriate.

EOT;
    return $msg;
  }
  /* ================================================================
   * Send all the contact emails
   * Currently set to send one email per contact
   */
  protected function sendEmails($send,$contacts)
  {
    foreach($contacts as $email => $items) {

      // For summary
      $gameFirst = null;
      $nameFirst = null;

      $content = null;

      foreach ($items as $item) {

        $gameFirst = $gameFirst ? $gameFirst : $item['game'];
        $nameFirst = $nameFirst ? $nameFirst : $item['name'];

        if ($content) {
					$content .= "\n========================================\n\n";
				}
				
        $content .= $this->renderEmailContentForGame($item['game']);
      }
      $content .= <<<EOT

Thanks for using Sportac.us!
http://sportac.us
EOT;
//This email was sent to all contacts associated with these games in Sportac.us.

      //$dt = \DateTime::createFromFormat('Y-m-d H:i:s', "{$gameFirst['date']} {$gameFirst['time']}");
      //$subject = sprintf("[Sportac.us] Game(s) Reminder #%d %s",
      //  $game['number'],
      //  $dt->format('l, F j, Y, g:i A')
      //);
      $subject = "[Sportac.us] Game Reminder(s)";
      echo sprintf("Send: %d %s %s %s\n", $send, $email, $nameFirst, $subject);

      if ($send) {
        /** @var \Swift_Message $message */
        $message = $this->mailer->createMessage();
        $message->setFrom('notification@sportac.us', 'Sportac.us Scheduling System');
        $message->setSender('notification@sportac.us');
        $message->setTo($email, $nameFirst);
        $message->setCc("john.price@ayso894.net", "John Price"); // FIXME: use parameter
        $message->setSubject($subject);
        $message->setBody($content);

        $this->mailer->send($message);
      } else {
        $content = "Sent to $nameFirst <$email>\n\n" . $content;
        /** @var \Swift_Message $message */
        $message = $this->mailer->createMessage();
        $message->setFrom('notification@sportac.us', 'Sportac.us Scheduling System');
        $message->setSender('notification@sportac.us');
        $message->setTo("john.price@ayso894.net", "John Price"); // FIXME: use parameter
        $message->setSubject($subject);
        $message->setBody($content);

        //$this->mailer->send($message); // ah - Don't like this
      }
    }
  }
  /* ========================================================
   * Main Entry Point
   *
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $env       = $input->getOption('env');
    $send      = $input->getOption('send') ? 1 : 0;
    $date      = $input->getOption('date');
    $projectId = $input->getOption('project');

    $date = $date ? $date : date('Y-m-d',strtotime('tomorrow'));

    $gameIds = $this->findGameIds([
      'date'       => $date,
      'project_id' => $projectId,
    ]);
    //$gameIds[] = 8436;
    //$gameIds = [8241,8240];
    if (count($gameIds) != 0) {
      echo sprintf(
                   "Game Email Reminders, Env: %s, Send: %d, Project: %d, Date: %s, Games: %d\n",
                   $env,$send,$projectId,$date,count($gameIds)
                   );
    }
    $games = $this->repository->findForIds($gameIds);
    //print_r($games);
    $contacts = [];
    foreach($games as $game) {
      $gameId = $game['id'];
      foreach($game['project_game_teams'] as $gameTeam) {
        $team = isset($gameTeam['project_team']) ? $gameTeam['project_team'] : null;
        if ($team) {
          if ($team['coach_email']) {
            $contacts[$team['coach_email']][$gameId] = [ 'name' => $team['coach_name'], 'game' => $game];
          }
          if ($team['poc_email']) {
            $contacts[$team['poc_email']][$gameId] = ['name' => null, 'game' => $game];
          }
        }
      }
      foreach($game['project_game_officials'] as $gameOfficial) {
        $official = isset($gameOfficial['project_official']) ? $gameOfficial['project_official'] : null;
        if ($official) {
          if ($official['email'] && $official['option_reminder_email']) {
            $name = "{$official['name_first']} {$official['name_last']}";
            $contacts[$official['email']][$gameId] = ['name' => $name, 'game' => $game];
          }
        }
      }
    }
    $this->sendEmails($send,$contacts);
  }
}
