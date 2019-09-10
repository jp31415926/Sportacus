<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Export;

use Cerad\Component\Excel\Reporter;

class ExporterExcel extends Reporter
{
  protected $repository;

  public function __construct(ExportRepository $repository)
  {
    $this->repository = $repository;
  }
  /* =====================================================
   * Column Definitions
   */
  protected $columns = [
    'project_id' => [
      'title' => 'ProjectID',
      'width' =>  8,
      'title_justify' => 'center',
      'value_justify' => 'center',
    ],
    'game_id' => [
      'title' => 'GameID',
      'width' =>  8,
      'title_justify' => 'center',
      'value_justify' => 'center',
    ],
    'team_id' => [
      'title' => 'TeamID',
      'width' =>  8,
      'title_justify' => 'center',
      'value_justify' => 'center',
    ],
    'game' => [
      'title' => 'Number',
      'width' =>  8,
      'title_justify' => 'center',
      'value_justify' => 'center',
    ],
    'date' => [
      'title' => 'Date',
      'width' => 10,
      'title_justify' => 'center',
      'value_justify' => 'right',
      'type'   => 'date',
      'format' => 'dd-mmm-yy',
    ],
    'dow'  => [
      'title' => 'DOW',
      'width' =>  6,
      'title_justify' => 'center',
      'value_justify' => 'center',
      'type' => 'dow'
    ],
    'time' => [
      'title' => 'Time',
      'width' => 10,
      'title_justify' => 'center',
      'value_justify' => 'right',
      'type' => 'time'
    ],
    'location' => [
      'title' => 'Location',
      'width' => 20,
      'title_justify' => 'left',
      'value_justify' => 'left',
    ],
    'region' => [
      'title' => 'Region',
      'width' =>  8,
      'title_justify' => 'left',
      'value_justify' => 'left',
    ],
    'level' => [
      'title' => 'Division',
      'width' =>  8,
      'title_justify' => 'left',
      'value_justify' => 'left',
    ],
    'length' => [
      'title' => 'Length',
      'width' =>  8,
      'title_justify' => 'center',
      'value_justify' => 'center',
    ],
    'length_slot' => [
      'title' => 'TSLength',
      'width' =>  8,
      'title_justify' => 'center',
      'value_justify' => 'center',
    ],
    'home_team' => [
      'title' => 'Home',
      'width' => 30,
      'title_justify' => 'left',
      'value_justify' => 'left',
    ],
    'away_team' => [
      'title' => 'Away',
      'width' => 30,
      'title_justify' => 'left',
      'value_justify' => 'left',
    ],
    'team_name' => [
      'title' => 'Team Name',
      'width' => 30,
      'title_justify' => 'left',
      'value_justify' => 'left',
    ],
    'coach_name' => [
      'title' => 'Coach Name',
      'width' => 20,
      'title_justify' => 'left',
      'value_justify' => 'left',
    ],
    'coach_email' => [
      'title' => 'Coach Email',
      'width' => 30,
      'title_justify' => 'left',
      'value_justify' => 'left',
    ],
    'coach_phone' => [
      'title' => 'Coach Phone',
      'width' => 16,
      'title_justify' => 'left',
      'value_justify' => 'left',
      'type' => 'phone',
    ],
    'manager_email' => [
      'title' => 'Manager Email',
      'width' => 30,
      'title_justify' => 'left',
      'value_justify' => 'left',
    ],
  ];
  protected function generateGamesSheet(\PHPExcel_Worksheet $ws,$games)
  {
    $ws->setTitle('Games');

    $columns = array_replace($this->columns,[
      'state' => [
        'title' => 'State',
        'width' => 10,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'pub' => [
        'title' => 'Pub',
        'width' => 5,
        'title_justify' => 'center',
        'value_justify' => 'center',
      ],
      'notes' => [
        'title' => 'Short Note',
        'width' => 20,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'home_team_score' => [
        'title' => 'HTS',
        'width' => 4,
        'title_justify' => 'center',
        'value_justify' => 'center',
      ],
      'away_team_score' => [
        'title' => 'ATS',
        'width' => 4,
        'title_justify' => 'center',
        'value_justify' => 'center',
      ],
    ]);
    $headers = [
      'project_id','game_id','state','pub','dow','game','date','time','location',
      'home_team','away_team','length','length_slot','level','region','notes','home_team_score','away_team_score',
    ];
    $this->writeHeaders($ws, $columns, 1, $headers);
    $row = 2;

    foreach($games as $game)
    {
      $values = [
        'project_id'  => $game['project_id'],
        'game_id'     => $game['id'],
        'state'       => $game['state'],
        'pub'         => $game['published'],
        'dow'         => $game['date'],
        'game'        => $game['number'],
        'date'        => $game['date'],
        'time'        => $game['time'],
        'location'    => $game['location_name'],
        'home_team'   => $game['teams']['home']['project_team_name'],
        'away_team'   => $game['teams']['away']['project_team_name'],
        'length'      => $game['length'],
        'length_slot' => $game['length_slot'],
        'level'       => $game['level_name'],
        'region'      => $game['region_name'],
        'notes'       => $game['notes'],

        'home_team_score' => $game['teams']['home']['project_game_team_score'],
        'away_team_score' => $game['teams']['away']['project_game_team_score'],
      ];
      $this->writeValues($ws,$columns,$row++,$values);
    }
  }
  protected function generateTeamsSheet(\PHPExcel_Worksheet $ws,$teams)
  {
    $ws->setTitle('Teams');

    $headers = [
      'project_id','team_id','region','level','team_name',
      'coach_name','coach_email','coach_phone','manager_email',
    ];
    $this->writeHeaders($ws, $this->columns, 1, $headers);
    $row = 2;

    foreach($teams as $team)
    {
      $values = [
        'project_id'    => $team['project_id'],
        'team_id'       => $team['id'],
        'region'        => $team['region_name'],
        'level'         => $team['level_name'],
        'team_name'     => $team['name'],
        'coach_name'    => $team['coach_name'],
        'coach_email'   => $team['coach_email'],
        'coach_phone'   => $team['coach_phone'],
        'manager_email' => $team['manager_email'],
      ];
      $this->writeValues($ws,$this->columns,$row++,$values);
    }
  }
  protected function generateLocationsSheet(\PHPExcel_Worksheet $ws,$locations)
  {
    $columns = array_replace($this->columns,[
      'id' => [
        'title' => 'LocationID',
        'width' =>  10,
        'title_justify' => 'center',
        'value_justify' => 'center',
      ],
      'name' => [
        'title' => 'Location Name',
        'width' => 20,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'name_long' => [
        'title' => 'Location Name Long',
        'width' => 35,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'url' => [
        'title' => 'Location URL',
        'width' => 30,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'poc_name' => [
        'title' => 'POC Name',
        'width' => 20,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'poc_email' => [
        'title' => 'POC Email',
        'width' => 20,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'poc_phone' => [
        'title' => 'POC Phone',
        'width' => 16,
        'title_justify' => 'left',
        'value_justify' => 'left',
        'type' => 'phone',
      ],
      'street1' => [
        'title' => 'Street 1',
        'width' => 20,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'street2' => [
        'title' => 'Street 2',
        'width' => 10,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'city' => [
        'title' => 'City',
        'width' => 10,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'state' => [
        'title' => 'State',
        'width' => 10,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'zip' => [
        'title' => 'Zip',
        'width' => 10,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'latitude' => [
        'title' => 'Latitude',
        'width' => 20,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'longitude' => [
        'title' => 'Longitude',
        'width' => 20,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
    ]);

    $ws->setTitle('Locations');

    $headers = [
      'project_id','id','name','name_long',
      'street1','street2','city','state','zip','latitude','longitude',
      'url','poc_name','poc_email','poc_phone',
    ];
    $this->writeHeaders($ws, $columns, 1, $headers);
    $row = 2;

    foreach($locations as $location)
    {
      $values = [
        'project_id' => $location['project_id'],
        'id'         => $location['id'],
        'name'       => $location['name'],
        'name_long'  => $location['name_long'],
        'street1'    => $location['street1'],
        'street2'    => $location['street2'],
        'city'       => $location['city'],
        'state'      => $location['state'],
        'zip'        => $location['zip'],
        'latitude'   => $location['latitude'],
        'longitude'  => $location['longitude'],
        'url'        => $location['url'],
        'poc_name'   => $location['poc_name'],
        'poc_email'  => $location['poc_email'],
        'poc_phone'  => $location['poc_phone'],
      ];
      $this->writeValues($ws,$columns,$row++,$values);
    }
  }
  protected function generateRegionsSheet(\PHPExcel_Worksheet $ws,$regions)
  {
    $columns = array_replace($this->columns,[
      'id' => [
        'title' => 'RegionID',
        'width' =>  10,
        'title_justify' => 'center',
        'value_justify' => 'center',
      ],
      'name' => [
        'title' => 'Name',
        'width' => 10,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'name_long' => [
        'title' => 'Name Long',
        'width' => 34,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'poc_name' => [
        'title' => 'POC Name',
        'width' => 20,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'poc_email' => [
        'title' => 'POC Email',
        'width' => 30,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'ref_admin_name' => [
        'title' => 'Ref Admin Name',
        'width' => 20,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'ref_admin_email' => [
        'title' => 'Ref Admin Email',
        'width' => 30,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
    ]);

    $ws->setTitle('Regions');

    $headers = [
      'project_id','id','name','name_long',
      'poc_name','poc_email',
      'ref_admin_name','ref_admin_email',
    ];
    $this->writeHeaders($ws, $columns, 1, $headers);
    $row = 2;

    foreach($regions as $region)
    {
      $values = [
        'project_id'     => $region['project_id'],
        'id'             => $region['id'],
        'name'           => $region['name'],
        'name_long'      => $region['name_long'],
        'poc_name'       => $region['poc_name'],
        'poc_email'      => $region['poc_email'],
        'ref_admin_name' => $region['ref_admin_name'],
        'ref_admin_email'=> $region['ref_admin_email'],
      ];
      $this->writeValues($ws,$columns,$row++,$values);
    }
  }
  protected $points = [
    'title' => null,
    'width' =>  6,
    'title_justify' => 'center',
    'value_justify' => 'center',
  ];
  protected function generateAgeGroupsSheet(\PHPExcel_Worksheet $ws,$levels)
  {
    $columns = array_replace($this->columns,[
      'id' => [
        'title' => 'AgeGroupID',
        'width' =>  8,
        'title_justify' => 'center',
        'value_justify' => 'center',
      ],
      'region' => [
        'title' => 'Region',
        'width' => 10,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'name' => [
        'title' => 'Name',
        'width' => 10,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'age' => [
        'title' => 'Age',
        'width' => 10,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'difficulty' => [
        'title' => 'Difficulty',
        'width' => 10,
        'title_justify' => 'center',
        'value_justify' => 'center',
      ],
      'gender' => [
        'title' => 'Gender',
        'width' => 10,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'division' => [
        'title' => 'Division',
        'width' => 10,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'pm'   => array_merge($this->points,['title' => 'PM']),
      'pr1'  => array_merge($this->points,['title' => 'PR1']),
      'pr1y' => array_merge($this->points,['title' => 'PR1Y']),
      'pr2'  => array_merge($this->points,['title' => 'PR2']),
      'pr2y' => array_merge($this->points,['title' => 'PR2Y']),
      'pr3'  => array_merge($this->points,['title' => 'PR3']),
      'pr3y' => array_merge($this->points,['title' => 'PR3Y']),
      'ptg'  => array_merge($this->points,['title' => 'PTG']),
    ]);

    $ws->setTitle('AgeGroups');

    $headers = [
      'project_id','id','region','difficulty','name','age','gender','division',
      'pm','pr1','pr1y','pr2','pr2y','pr3','pr3y','ptg',
    ];
    $this->writeHeaders($ws, $columns, 1, $headers);
    $row = 2;

    foreach($levels as $level)
    {
      $values = [
        'project_id' => $level['project_id'],
        'id'         => $level['id'],
        'region'     => $level['region_name'],
        'difficulty' => $level['difficulty'],
        'name'       => $level['name'],
        'age'        => $level['name'],
        'gander'     => null,
        'division'   => null,

        'pm'   => $level['pm'  ],
        'pr1'  => $level['pr1' ],
        'pr1y' => $level['pr1y'],
        'pr2'  => $level['pr2' ],
        'pr2y' => $level['pr2y'],
        'pr3'  => $level['pr3' ],
        'pr3y' => $level['pr3y'],
        'ptg'  => $level['ptg' ],
      ];
      $this->writeValues($ws,$columns,$row++,$values);
    }
  }
  protected function generateLevelsSheet(\PHPExcel_Worksheet $ws,$levels)
  {
    $columns = array_replace($this->columns,[
      'id' => [
        'title' => 'LevelID',
        'width' =>  8,
        'title_justify' => 'center',
        'value_justify' => 'center',
      ],
      'level_key' => [
        'title' => 'LevelKey',
        'width' => 12,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'name' => [
        'title' => 'Name',
        'width' => 10,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'title' => [
        'title' => 'Title',
        'width' => 20,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'age' => [
        'title' => 'Age',
        'width' => 10,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'gender' => [
        'title' => 'Gender',
        'width' => 10,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'division' => [
        'title' => 'Division',
        'width' => 10,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'game_slot_length' => [
        'title' => 'Length',
        'width' => 10,
        'title_justify' => 'center',
        'value_justify' => 'center',
      ],
      'crew_type' => [
        'title' => 'Crew',
        'width' => 10,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
    ]);

    $ws->setTitle('Levels');

    $headers = [
      'project_id','id','level_key',
      'name','title','age','gender','division',
      'game_slot_length','crew_type',
    ];
    $this->writeHeaders($ws, $columns, 1, $headers);
    $row = 2;

    foreach($levels as $level)
    {
      $values = [
        'project_id' => $level['project_id'],
        'id'         => $level['id'],
        'level_key'  => $level['level_key'],
        'name'       => $level['name'],
        'title'      => $level['title'],
        'age'        => $level['age'],
        'gender'     => $level['gender'],
        'division'   => $level['division'],

        'game_slot_length' => $level['game_slot_length'],
        'crew_type'        => $level['crew_type'],
      ];
      $this->writeValues($ws,$columns,$row++,$values);
    }
  }
  protected function generateProjectsSheet(\PHPExcel_Worksheet $ws,$projects)
  {
    $columns = array_replace($this->columns,[
      'id' => [
        'title' => 'ProjectID',
        'width' =>  8,
        'title_justify' => 'center',
        'value_justify' => 'center',
      ],
      'name' => [
        'title' => 'Name',
        'width' => 22,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'name_long' => [
        'title' => 'Name Long',
        'width' => 34,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'date_beg' => [
        'title' => 'Date Begin',
        'width' => 12,
        'title_justify' => 'right',
        'value_justify' => 'right',
        'type'   => 'date',
        'format' => 'dd-mmm-yy'
      ],
      'date_end' => [
        'title' => 'Date End',
        'width' => 12,
        'title_justify' => 'right',
        'value_justify' => 'right',
        'type'   => 'date',
        'format' => 'dd-mmm-yy'
      ],
      'sport' => [
        'title' => 'Sport',
        'width' => 10,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'trr' => [
        'title' => 'TRR',
        'width' => 6,
        'title_justify' => 'center',
        'value_justify' => 'center',
      ],
      'srr' => [
        'title' => 'SRR',
        'width' => 6,
        'title_justify' => 'center',
        'value_justify' => 'center',
      ],
    ]);

    $ws->setTitle('Projects');

    $headers = [
      'id','name','name_long','date_beg','date_end','sport','trr','srr',
    ];
    $this->writeHeaders($ws, $columns, 1, $headers);
    $row = 2;

    foreach($projects as $project)
    {
      $values = [
        'id'        => $project['id'],
        'name'      => $project['name'],
        'name_long' => $project['name_long'],
        'date_beg'  => $project['date_beg'],
        'date_end'  => $project['date_end'],
        'sport'     => $project['sport'],
        'trr'       => $project['trr'],
        'srr'       => $project['srr'],
      ];
      $this->writeValues($ws,$columns,$row++,$values);
    }
  }
  protected function generateOfficialPositionsSheet(\PHPExcel_Worksheet $ws,$positions)
  {
    $columns = array_replace($this->columns,[
      'project_id' => [
        'title' => 'ProjectID',
        'width' =>  8,
        'title_justify' => 'center',
        'value_justify' => 'center',
      ],
      'project_name' => [
        'title' => 'Project Name',
        'width' =>  14,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'id' => [
        'title' => 'PosID',
        'width' =>  6,
        'title_justify' => 'center',
        'value_justify' => 'center',
      ],
      'name' => [
        'title' => 'Name',
        'width' => 16,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'name_short' => [
        'title' => 'Name Short',
        'width' => 12,
        'title_justify' => 'left',
        'value_justify' => 'left',
      ],
      'da' => [
        'title' => 'DA',
        'width' => 6,
        'title_justify' => 'center',
        'value_justify' => 'center',
      ],
      'dv' => [
        'title' => 'DV',
        'width' => 6,
        'title_justify' => 'center',
        'value_justify' => 'center',
      ],
      'dr' => [
        'title' => 'DR',
        'width' => 6,
        'title_justify' => 'center',
        'value_justify' => 'center',
      ],
    ]);

    $ws->setTitle('OfficialPositions');

    $headers = [
      'project_id','project_name','id','name','name_short','da','dv','dr',
    ];
    $this->writeHeaders($ws, $columns, 1, $headers);
    $row = 2;

    foreach($positions as $position)
    {
      $values = [
        'project_id'   => $position['project_id'],
        'project_name' => $position['project_name'],
        'id'           => $position['id'],
        'name'         => $position['name'],
        'name_short'   => $position['name_short'],
        'da'           => $position['diff_avail'],
        'dv'           => $position['diff_visible'],
        'dr'           => $position['diff_required'],
      ];
      $this->writeValues($ws,$columns,$row++,$values);
    }
  }

  /* ==========================================================
   * Main entry point
   */
  public function generate($criteria)
  {
    $this->ss = $ss = $this->createSpreadSheet();

    $games     = $this->repository->loadProjectGames    ($criteria);
    $teams     = $this->repository->loadProjectTeams    ($criteria);
    $levels    = $this->repository->loadProjectLevels   ($criteria);
    $regions   = $this->repository->loadProjectRegions  ($criteria);
    $projects  = $this->repository->loadProjects        ($criteria);
    $locations = $this->repository->loadProjectLocations($criteria);
    $ageGroups = $this->repository->loadProjectAgeGroups($criteria);

    $officialPositions = $this->repository->loadProjectOfficialPositions($criteria);

    $si = 0;

    $this->generateGamesSheet    ($ss->createSheet($si++),$games);
    $this->generateTeamsSheet    ($ss->createSheet($si++),$teams);
    $this->generateLocationsSheet($ss->createSheet($si++),$locations);
    $this->generateRegionsSheet  ($ss->createSheet($si++),$regions);
    $this->generateAgeGroupsSheet($ss->createSheet($si++),$ageGroups);
    $this->generateLevelsSheet   ($ss->createSheet($si++),$levels);

    $this->generateOfficialPositionsSheet($ss->createSheet($si++),$officialPositions);

    $this->generateProjectsSheet ($ss->createSheet($si),$projects);

    // Finish up
    $ss->setActiveSheetIndex(0);
        
    return $ss;
  }
}
