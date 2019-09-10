<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Update;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\DBAL\Connection;

class NumbersCommand extends Command
{
  /* @var Connection */
  protected $dbConn;

  public function __construct(Connection $dbConn)
  {
    $this->dbConn = $dbConn;

    parent::__construct();
  }
  protected function configure()
  {
    $this->setName       ('cerad_project_game_update_numbers');
    $this->setDescription('Set the number attribute for legacy games');
  }
  public function loadGames(Connection $dbConn)
  {
    $qb = $dbConn->createQueryBuilder();

    $qb->addSelect([
      'game.id     AS id',
      'game.idstr  AS idstr',
      'game.number AS number',
    ]);

    $qb->from('Game','game');

    return $qb->execute()->fetchAll();
  }
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $dbConn = $this->dbConn;

    $games = $this->loadGames($dbConn);
    $updated = 0;

    echo sprintf("Scanning %d games\n",count($games));

    foreach($games as $game) {
      if ($game['number']) {
        continue;
      }
      $number = (integer)$game['idstr'];
      if (!$number) {
        $number = $game['id'];
      }
      $dbConn->update('Game',['number' => $number],['id' => $game['id']]);
      $updated++;

      if (($updated % 100) === 0) {
        echo sprintf("Updated %d numbers\r",$updated);
      }
    }
    echo sprintf("Updated %d numbers\n",$updated);
  }
}
?>
