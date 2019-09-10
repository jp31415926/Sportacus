<?php

namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Import;

use Cerad\Component\Excel\Loader;
use Cerad\Component\Excel\ReaderFactory;

class LoaderExcel extends Loader
{
  protected $repository;
  
  public function __construct(LoaderRepository $repository)
  {
    $this->repository = $repository;
  }
  protected $record = [
    'number'=> ['cols' => ['Game','Number'],'req' => false, 'default' => null],

    'state' => ['cols' => ['State','Status'], 'req' => false, 'default' => null],
    'pub'   => ['cols' => ['Pub','Published'],'req' => false, 'default' => 1],

    'date'  => ['cols' => 'Date',    'req' => true],
    'time'  => ['cols' => 'Time',    'req' => true],

    'region'=> ['cols' => 'Region',  'req' => true],
    'age'   => ['cols' => 'Division','req' => true],
    'field' => ['cols' => 'Location','req' => true],

    'game_length' => ['cols' => 'Length',  'req' => false, 'default' => 0,],
    'slot_length' => ['cols' => 'TSLength','req' => false, 'default' => 0,],

    'notes' => ['cols' => ['Short Note','Notes'],'req' => false],

    'homeTeamName' => ['cols' => ['Home','Home Team'], 'req' => true],
    'awayTeamName' => ['cols' => ['Away','Away Team'], 'req' => true],
  ];

  protected $lengths = [
    ''    => array('game' =>  0, 'slot' =>   0),
    'U05' => array('game' => 30, 'slot' =>  60),
    'U06' => array('game' => 30, 'slot' =>  60),
    'U07' => array('game' => 30, 'slot' =>  60),
    'U08' => array('game' => 40, 'slot' =>  60),
    'U10' => array('game' => 50, 'slot' =>  75),
    'U12' => array('game' => 60, 'slot' =>  90),
    'U14' => array('game' => 70, 'slot' =>  90),
    'U16' => array('game' => 80, 'slot' => 120),
    'U19' => array('game' => 90, 'slot' => 120),
  ];
  protected $states = [
    'Inactive'   => 0,
    'Normal'     => 1,
    'Complete'   => 2,
    'Cancelled'  => 3,
    'Suspended'  => 4,
    'Rained out' => 5,
    'Forfeit'    => 6,
    'Postponed'  => 7,
  ];

  protected $games = [];

  /* ============================================================
   * Convert item to game
   * Resulting names need to match the database column names
   */
  protected function processItem($results, $project, $item, $rownum)
  {
    $repository = $this->repository;

    // Skip blank lines
    foreach(['date','time','field','age','region'] as $key) {
      if (!$item[$key]) return null;
    }
    $projectId = $project['id'];

    // Match column names
    $game = [
      'id'         => null,
      'project_id' => $projectId,
      'number'     => $item['number'],
      'date'       => $this->processDate($item['date']),
      'time'       => $this->processTime($item['time']),
      'age'        => $item['age'],
      'short_note' => $item['notes'],
      'published'  => $item['pub'],
    ];
    // jp: want to make sure date is within project dates
    if (($game['date'] < $project['start_date']) || ($game['date'] > $project['end_date'])) {
      $results->errors[] = sprintf('ERR_DAT %d Date %s is out of range for project', $rownum, $item['date']);
      $game['date'] = null;
    }
    $game['field'] = $field = $repository->findField($item['field']);
    if (!$field) {
      $results->errors[] = sprintf('ERR_LOC %d Missing Location %s',$rownum,$item['field']);
      $game['field'] = ['id' => null, 'name' => null];
    }
    $game['region'] = $region = $repository->findRegion($item['region']);
    if (!$region) {
      $results->errors[] = sprintf('ERR_REG %d Missing Region %s', $rownum, $item['region']);
      $game['region'] = ['id' => null, 'name' => null];
    }
    $age = $game['age'];
    $game['level'] = $level = $repository->findLevel($projectId,$item['region'],$age);
    if (!$level) {
      $results->errors[] = sprintf('ERR_AGE %d Missing Age Group %d %s %s', $rownum, $projectId,$item['region'],$age);
      $game['level'] = ['id' => null, 'name' => null];
    }
    // Lengths
    $game['length']         = $gameLength = $item['game_length'];
    $game['timeslotlength'] = $slotLength = $item['slot_length'];
    if (!$gameLength)
    {
      if (isset($this->lengths[$age])) {
        $game['length'] = $this->lengths[$age]['game'];
      } else {
        $results->errors[] = sprintf('ERR_LEN %d Missing Game Length For Age %s', $rownum, $age);
      }
    }
    if (!$slotLength)
    {
      if (isset($this->lengths[$age])) {
        $game['timeslotlength'] = $this->lengths[$age]['slot'];
      } else {
        $results->errors[] = sprintf('ERR_SLL %d Missing Slot Length For Age %s', $rownum, $age);
      }
    }
    // state vs status
    $state = isset($item['state']) ? $item['state'] : 'Inactive';
    if (isset($this->states[$state])) {
      $game['status'] = $this->states[$state];
    } else {
      $results->errors[] = sprintf('ERR_STA %d Invalid Game Status %s', $rownum, $state);
      $game['status'] = -1;
    }
    // Teams
    foreach(['home','away'] as $slot) {
      $name = $item[$slot . 'TeamName'];
      $team = $repository->findTeam($projectId,$name);
      if (!$team && $name) {
        $results->errors[] = sprintf('ERR_TEA %d Missing Team %d %s', $rownum, $projectId, $name);
      }
      if (!$team) {
        $team = ['slot' => $slot, 'id' => null, 'name' => null];
      }
      $game['teams'][$slot] = $team;
    }
    // Officials
    $game['officials'] = [];
    switch($age) {
      case 'U05': case 'U06':
        $slots = [];
        break;
      case 'U07': case 'U08':
        $slots = ['ref'];
        break;
      default:
        $slots = ['ref','ar1','ar2'];
    }
    foreach($slots as $slot) {
      $game['officials'][$slot] = [
        'slot' => $slot,
      ];
    }
    // Have game in same slot within the import file?
    $key = sprintf('%s %s %s',$game['date'],$game['time'],$item['field']);
    if (isset($this->games[$key])) {
      $results->errors[] = sprintf('ERR_DUP %d Duplicate game time slot %s %s %s', $rownum,
        $game['date'],$game['time'],$item['field']);
    }
    $this->games[$key] = true;

    // Have game in the same slot within the database?
    $gameId = $repository->findGameIdForDateTimeFieldSlot($game['date'],$game['time'],$game['field']['id']);
    if ($gameId) {
      $results->errors[] = sprintf('ERR_DUP %d Duplicate game in database %d %s %s %s %s %s',$rownum,
        $gameId, $game['date'],$game['time'],
        $game['region']['name'],$game['level']['name'],$game['field']['name']
      );
    }
    return $game;
  }
  /* =========================================================
   * Main entry point
   *
   */
  public function load(array $params)
  {
    // Results
    $results = new LoaderResults();
    $results->filename  = $filename  = $params['filename'];
    $results->basename  = $basename  = $params['basename'];
    $results->worksheet = $worksheet = $params['worksheet'];

    // Default project
    $projectId = $params['projectId'];
    $project = $this->repository->findProject($projectId);
    if (!$project) {
      $results->errors[] = sprintf('ERR_PRJ 0 Invalid default project %d',$projectId);
      return $results;
    }
    $results->project = $project;

    // Load up the spreadsheet
    $reader = ReaderFactory::create($filename);
    $excel  = $reader->load($filename);
    $ws     = $worksheet ? $excel->getSheetByName($worksheet) : $excel->getSheet(0);
    $rows   = $ws->toArray();

    // Process the header row
    $header = array_shift($rows);
    $headerErrors = $this->processHeaderRow($header);
    if (count($headerErrors)) {
      $results->errors = $headerErrors;
      $r='1 ';
      foreach($header as $value) {
        $r .= ','.$value;
      }
      $results->errors[] = $r; //jp TODO: make optional
      return $results;
    }
    // Build a list of games
    $rownum = 2;
    foreach($rows as $row)
    {
      $item = $this->processDataRow($row);
      $errs = count($results->errors);
      $game = $this->processItem($results, $project, $item, $rownum);
      if ($errs != count($results->errors)) {
        $r=$rownum;
        foreach($row as $value) {
          $r .= ','.$value;
        }
        $results->errors[] = $r; //jp TODO: make optional
        ++$rownum;
      }
      if ($game) {
        $results->games[] = $game;
      }
    }
    return $results;
  }
}
