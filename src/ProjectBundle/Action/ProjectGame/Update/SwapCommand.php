<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Update;

use Symfony\Component\Console\Command\Command;
//  Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\DBAL\Connection;

class SwapCommand extends Command
{
  /**
   * @var Connection
   */
  protected $dbConn;

  public function __construct(Connection $dbConn)
  {
    $this->dbConn = $dbConn;

    parent::__construct();
  }
  protected function configure()
  {
    $this->setName       ('cerad_project_game_swap');
    $this->setDescription('Swap referee standby/mentor');
  }
  public function loadOfficials(Connection $dbConn)
  {
    $qb = $dbConn->createQueryBuilder();

    $qb->addSelect([
      'official.id   AS id',
      'official.slot AS slot'
    ]);

    $qb->from('project_game_officials','official');

    $qb->leftJoin('official','Game', 'game','game.id = official.project_game_id');

    $qb->andWhere('game.project_id = 20');
    $qb->andWhere("official.slot IN('standby','mentor')");

    return $qb->execute()->fetchAll();
  }
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $dbConn = $this->dbConn;

    $officials = $this->loadOfficials($dbConn);

    // Do this to avoid constraint violations
    foreach($officials as $official) {
      $id = $official['id'];
      switch ($official['slot']) {
        case 'standby':
          $dbConn->update('project_game_officials', ['slot' => 'standbyx'], ['id' => $id]);
          break;
        case 'mentor':
          $dbConn->update('project_game_officials', ['slot' => 'mentorxx'], ['id' => $id]);
          break;
      }
    }
    foreach($officials as $official) {
      $id = $official['id'];
      switch($official['slot']) {
        case 'standby':
          $dbConn->update('project_game_officials',['slot' => 'mentor'],['id' => $id]);
          break;
        case 'mentor':
          $dbConn->update('project_game_officials',['slot' => 'standby'],['id' => $id]);
          break;
      }
    }
    echo sprintf("Swapped %d referee slots\n",count($officials));
    //print_r($officials);
  }
}
?>
