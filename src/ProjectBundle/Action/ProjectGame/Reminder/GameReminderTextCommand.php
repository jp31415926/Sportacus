<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Reminder;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\DBAL\Connection;
use Cerad\Bundle\ProjectBundle\EntityRepository\ProjectGameRepositorySql as Repository;

class GameReminderTextCommand extends Command
{
  /** @var Connection  */
  protected $dbConn;
  protected $repository;

  protected $tropoDevKey;
  protected $tropoProdKey;
  protected $tropoUrl = 'http://api.tropo.com/1.0/sessions?action=create&token=';

  public function __construct(Repository $repository, $tropoDevKey, $tropoProdKey)
  {
    $this->tropoDevKey  = $tropoDevKey;
    $this->tropoProdKey = $tropoProdKey;

    $this->repository = $repository;
    $this->dbConn     = $repository->getDatabaseConnection();

    parent::__construct();
  }
  protected function configure()
  {
    $this->setName('cerad_project_game_reminder_text');
    $this->setDescription('Send Upcoming Game Text Reminders');

    $this->addOption('send',   null, InputOption::VALUE_REQUIRED, 'Actually Send the Messages',false);
    $this->addOption('date',   null, InputOption::VALUE_REQUIRED, 'date yyyy-mm-dd',null);
    $this->addOption('time',   null, InputOption::VALUE_REQUIRED, 'time hh:mm',     null);
    $this->addOption('project',null, InputOption::VALUE_REQUIRED, 'Project ID',     null);

    $help = <<<EOT
To send texts for upcoming games:
app/console cerad_project_game_reminder_text --env=prod --send=1 --project=20 --date=20150818 --time=09:00
All the parameters are optional.

--date Defaults to current date.  Explicitly setting it is useful for testing.
--time
      Defaults to current time.
      Texts will be sent to games between time + 1 hour and time + 2 hour
--project Defaults to all games for the specified date/time.
--send=1  Is required to actually send the texts.  --send=0 or just leaving it out can be used for testing.
--env
  Defaults to the development enviroment.
  In development, texts are sent using the tropo_dev_key  parameter.
  In production,  texts are sent using the tropo_prod_key parameter.

One text will be sent per game per referee if the referee's option_reminder_text is set.

Texts will be sent for games with a status of 1.
*** The legacy app used a status of 2??? ***

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

    $qb->andWhere('game.status = 1'); // Suppose to be 2???

    $qb->andWhere('game.published = 1');

    $qb->leftJoin('game','AgeGroup','level','level.id = game.agegroup_id');
    $qb->andWhere('level.difficulty >= 60');

    $qb->andWhere('game.date = :date');
    $qb->setParameter('date',$criteria['date']);

    $qb->andWhere('game.time BETWEEN :time1 AND :time2');
    $qb->setParameter('time1',$criteria['times']['time1']);
    $qb->setParameter('time2',$criteria['times']['time2']);

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
  /* ========================================================
   * Send one text message
   *
   */
  protected function sendText($send,$game,$official)
  {
    if (!$official) return;

    $phone = $official['phone_cell'] ? $official['phone_cell'] : null;
    if (!$phone) return;

    $homeTeamName = isset($game['project_game_teams']['home']['project_team']['name']) ?
      $game['project_game_teams']['home']['project_team']['name'] :
      null;

    $awayTeamName = isset($game['project_game_teams']['away']['project_team']) ?
      $game['project_game_teams']['home']['project_team']['name'] :
      null;

    $fieldName = isset($game['location']) ? $game['location']['name'] : null;

    $dt = \DateTime::createFromFormat('Y-m-d H:i:s',$game['date'] . ' ' . $game['time']);

    $msg = "Sportacus Game {$game['number']} {$homeTeamName} vs {$awayTeamName}, {$fieldName}, {$dt->format('g:i A')}";

    $url = sprintf('%s&num=%s&msg=%s',$this->tropoUrl,urlencode($phone),urlencode($msg));

    if (!$official['option_reminder_text'] && true) return;

    echo "{$send} {$official['username']} {$msg}\n";

    if (!$send) return;

    // Replace with Texter service
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //echo "Fetching $url\n";
    curl_exec($curl);
    curl_close($curl);
    usleep(1000000);
  }
  /* ========================================================
   * Main Entry Point
   *
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // Select tropo key based on enviroment
    $env = $input->getOption('env');
    switch($env) {
      case 'prod':
        $this->tropoUrl .= $this->tropoProdKey;
        break;
      default:
        $this->tropoUrl .= $this->tropoDevKey;
    }
    // Master override for testing
    $send = $input->getOption('send') ? true : false;

    // Defaults to null
    $projectId = $input->getOption('project');

    // Go through this nonsense so we can specify a date/time for testing
    $date = $input->getOption('date');
    $time = $input->getOption('time');

    $ts = strtotime('now');
    $date = $date ? $date : date('Y-m-d',$ts);
    $time = $time ? $time : date('H:i',  $ts);

    $dt1 = \DateTime::createFromFormat('Y-m-d H:i',$date . ' ' . $time);
    $dt2 = \DateTime::createFromFormat('Y-m-d H:i',$date . ' ' . $time);

    $dt1->add(new \DateInterval('PT1H'));
    $dt2->add(new \DateInterval('PT2H'));

    $times = [
      'time1' => $dt1->format('H:i:s'),
      'time2' => $dt2->format('H:i:s'),
    ];
    // Query for game ids
    $gameIds = $this->findGameIds([
      'date'       => $date,
      'times'      => $times,
      'project_id' => $projectId,
    ]);
    //$gameIds[] = 8436;
    //$gameIds = [8241,8240];
    echo sprintf(
      "Game Text Reminders, Send: %d, Project: %d, Date: %s, Games: %d\n",
      $send,$projectId,$date,count($gameIds)
    );
    $games = $this->repository->findForIds($gameIds);

    foreach($games as $game) {
      foreach ($game['project_game_officials'] as $gameOfficial) {
        $official = isset($gameOfficial['project_official']) ? $gameOfficial['project_official'] : null;
        $this->sendText($send, $game, $official);
      }
    }
  }
}
