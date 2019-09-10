<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Update;

use Symfony\Component\Console\Command\Command;
//  Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//  Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\DBAL\Connection;

class UpdateCommand extends Command
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
    $this->setName       ('cerad_project_game_update');
    $this->setDescription('Load the project game, teams and officials');
  }
  /*
  protected function updateSchema()
  {
    $dbUser = $this->getParameter('database_user');
    $dbPass = $this->getParameter('database_password');
    $dbName = $this->getParameter('database_name');
    $cmd = sprintf("mysql -u%s -p%s %s < %s/schema.sql",$dbUser,$dbPass,$dbName,__DIR__);//die($cmd);
    exec($cmd);
  }*/
  protected function getGameColumns()
  {
    return [
      'game.id         AS id',
      'game.idstr      AS num',
      //'game.date       AS date',
      //'game.time       AS time',
      //'game.length     AS length',
      //'game.timeslotlength AS lengthSlot',
      //'game.status     AS status',
      //'game.short_note AS shortNote',
      //'game.ref_notes  AS refNotes',

      'game.project_id  AS project_id',
      //'game.location_id AS fieldId',
      //'game.agegroup_id AS divId',
      //'game.region_id   AS regionId',

      'game.team1_id   AS home_team_id',
      'game.team2_id   AS away_team_id',
      'game.score1     AS home_team_score',
      'game.score2     AS away_team_score',

      'game.ref1_id AS ref1_id',
      'game.ref2_id AS ref2_id',
      'game.ref3_id AS ref3_id',
      'game.ref4_id AS ref4_id',
      'game.ref5_id AS ref5_id',
    ];
  }
  public function loadGames($dbConn)
  {
    $qb = $dbConn->createQueryBuilder();

    $qb->addSelect($this->getGameColumns());

    $qb->from('Game','game');

    //$qb->leftJoin('game','project', 'project','project.id = game.project_id');
    //$qb->leftJoin('game','location','field',  'field.id   = game.location_id');
    //$qb->leftJoin('game','agegroup','divx',   'divx.id    = game.agegroup_id');
    //$qb->leftJoin('game','region',  'region', 'region.id  = game.region_id');

    //$qb->leftJoin('game','team','homeTeam', 'homeTeam.id  = game.team1_id');
    //$qb->leftJoin('game','team','awayTeam', 'awayTeam.id  = game.team2_id');

    $games = $qb->execute()->fetchAll();
    return $games;
  }
  public function loadProjectOfficialSlots($dbConn)
  {
    $qb = $dbConn->createQueryBuilder();

    $qb->addSelect([
      'project.id   AS id',
      'project.name AS project_name',
      'official_position.name      AS official_position_name',
      'official_position.shortname AS official_position_slot',
    ]);

    $qb->from('Project','project');

    $qb->leftJoin(
      'project',
      'project_offpos',
      'project_official_positions',
      'project_official_positions.project_id = project.id');

    $qb->leftJoin(
      'project_official_positions',
      'OffPos',
      'official_position',
      'official_position.id = project_official_positions.offpos_id'
    );

    $rows = $qb->execute()->fetchAll();
    $projects = [];

    foreach($rows as $row)
    {
      $id = $row['id'];
      if (isset($projects[$id])) $project = $projects[$id];
      else {
        $project = [
          'project_name' => $row['project_name'],
          'slot_metas'   => [],
        ];
      }
      $slotMetaMap = [
        'CR'  => ['slot' => 'ref',    'required' => true,  ],
        'R1'  => ['slot' => 'ref1',   'required' => true,  ],
        'R2'  => ['slot' => 'ref2',   'required' => true,  ],
        'AR1' => ['slot' => 'ar1',    'required' => true,  ],
        'AR2' => ['slot' => 'ar2',    'required' => true,  ],
        '4TH' => ['slot' => '4th',    'required' => false, ],
        '5TH' => ['slot' => '5th',    'required' => false, ],
        'SBY' => ['slot' => 'standby','required' => false, ],
        'Mtr' => ['slot' => 'mentor', 'required' => false, ],

        'none' => ['slot' => null,],
        'NA'   => ['slot' => null,],
        'NA3'  => ['slot' => null,],
        'Na2'  => ['slot' => null,],
      ];
      $slotMeta = $slotMetaMap[$row['official_position_slot']];

      if ($slotMeta['slot'])  {
        $project['slot_metas'][] = $slotMeta;
      }
      $projects[$id] = $project;
    }
    //print_r($projects);die();
    return $projects;

  }
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    // $this->updateSchema();

    $dbConn = $this->dbConn;

    // Empty the tables
    foreach(['project_game_teams','project_game_officials'] as $tableName) {
      $dbConn->executeQuery(sprintf('DELETE FROM %s;',                  $tableName));
      $dbConn->executeQuery(sprintf('ALTER TABLE %s AUTO_INCREMENT = 1;',$tableName));
    }

    $projectOfficialSlots = $this->loadProjectOfficialSlots($dbConn);

    $games = $this->loadGames($dbConn);

    echo sprintf("Process Project Games %d\n",count($games));

    $count = 0;
    foreach($games as $game) {
      $count++;

      $teams = [
        'home' => [
          'slot' => 'home',
          'project_game_id' => $game['id'],
          'project_team_id' => $game['home_team_id'],
          'score'           => $game['home_team_score'],
          'source' => null,
        ],
        'away' => [
          'slot' => 'away',
          'project_game_id' => $game['id'],
          'project_team_id' => $game['away_team_id'],
          'score'           => $game['away_team_score'],
          'source' => null,
        ],
      ];
      foreach ($teams as $team) {
        $dbConn->insert('project_game_teams',$team);
      }
      foreach($projectOfficialSlots[$game['project_id']]['slot_metas'] as $pos => $slotMeta) {
        $officialId = $game[sprintf('ref%d_id',$pos + 1)];
        if ($officialId || $slotMeta['required']) {
          $official = [
            'slot'                => $slotMeta['slot'],
            'project_game_id'     => $game['id'],
            'project_official_id' => $officialId,
          ];
          $dbConn->insert('project_game_officials', $official);
        }
      }
      if (($count % 50) === 0) {
        echo sprintf("Games Processed %d\r",$count);
      }
    }
    echo sprintf("Games Processed %d\n",$count);
    return;
  }
}
?>
