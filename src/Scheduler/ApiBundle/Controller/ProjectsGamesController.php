<?php

namespace Scheduler\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Doctrine\DBAL\Connection as DbConn;
  
class ProjectsGamesController extends Controller
{
  protected $pin;
  protected $dbConn;

  public function __construct(DbConn $dbConn,$pin)
  {
    $this->pin = (integer)$pin;
    $this->dbConn = $dbConn;
  }
  protected function verifyAccess(Request $request)
  {
    $pin = (integer)$request->query->get('pin');
    if ($pin !== $this->pin) {
      throw new AccessDeniedException();
    }
  }
  protected function getGameColumns()
  {
    return array(
      'game.id         AS id',
      'game.number     AS num',
      'game.date       AS date',
      'game.time       AS time',
      'game.length     AS length',
      'game.timeslotlength AS lengthSlot',
      'game.status     AS status',
      'game.short_note AS shortNote',
      'game.ref_notes  AS refNotes',
      
      'game.project_id  AS projectId',
      'game.location_id AS fieldId',
      'game.agegroup_id AS divId',
      'game.region_id   AS regionId',
      
      'game.team1_id   AS homeTeamId',
      'game.team2_id   AS awayTeamId',
      'game.score1     AS homeTeamScore',
      'game.score2     AS awayTeamScore',
      
      'game.ref1_id AS ref1Id',
      'game.ref2_id AS ref2Id',
      'game.ref3_id AS ref3Id',
      'game.ref4_id AS ref4Id',
      'game.ref5_id AS ref5Id',
     );
  }
  public function createProjectGameQuery($projectId = null)
  {
    $qb = $this->dbConn->createQueryBuilder();
    
    $qb->addSelect($this->getGameColumns());
    
    $qb->from('Game','game');
    
    //$qb->leftJoin('game','project', 'project','project.id = game.project_id');
    //$qb->leftJoin('game','location','field',  'field.id   = game.location_id');
    //$qb->leftJoin('game','agegroup','divx',   'divx.id    = game.agegroup_id');
    //$qb->leftJoin('game','region',  'region', 'region.id  = game.region_id');
    
    //$qb->leftJoin('game','team','homeTeam', 'homeTeam.id  = game.team1_id');
    //$qb->leftJoin('game','team','awayTeam', 'awayTeam.id  = game.team2_id');

    if ($projectId) {
      $qb->andWhere('game.project_id = ' . $qb->createPositionalParameter($projectId));
    }
    return $qb;
  }
  public function findProjectGame($projectId,$gameNum)
  {
    $qb = $this->createProjectGameQuery($projectId);
    
    $qb->andWhere('game.number = ' . $qb->createPositionalParameter($gameNum));
  //die($qb->getSql());
    
    $games = $qb->execute()->fetchAll();
    return $this->loadGamesInfo($games);
    
  }
  public function findProjectGames($projectId,$date1 = null, $date2 = null)
  {
    $qb = $this->createProjectGameQuery($projectId);
    
    if ($date1) {
      $qb->andWhere('game.date >= ' . $qb->createPositionalParameter($date1));
    }
    if ($date2) {
      $qb->andWhere('game.date <= ' . $qb->createPositionalParameter($date2));
    }
    $games = $qb->execute()->fetchAll();
    return $this->loadGamesInfo($games);
  }
  public function loadGamesInfo($games)
  {
    $divIds     = array();
    $teamIds    = array();
    $fieldIds   = array();
    $regionIds  = array();
    $personIds  = array();
    $projectIds = array();
    
    foreach($games as $game) {

      if ($game['divId'])     $divIds    [$game['divId'    ]] = true;
      if ($game['fieldId'])   $fieldIds  [$game['fieldId'  ]] = true;
      if ($game['regionId'])  $regionIds [$game['regionId' ]] = true;
      if ($game['projectId']) $projectIds[$game['projectId']] = true;
      
      foreach(array('homeTeamId','awayTeamId') as $key) {
        if ($game[$key]) $teamIds[$game[$key]] = true;
      }
      foreach(array('ref1Id','ref2Id','ref3Id','ref4Id','ref5Id') as $key) {
        if ($game[$key]) $personIds[$game[$key]] = true;
      }
    }    
    $teams   = $this->findTeams  (array_keys($teamIds));
    $persons = $this->findPersons(array_keys($personIds));
    
    foreach($teams as $team) {
      if ($team['divId'])    $divIds   [$team['divId'   ]] = true;
      if ($team['regionId']) $regionIds[$team['regionId']] = true;
    }
    foreach($persons as $person) {
      if ($person['regionId']) $regionIds[$person['regionId']] = true;
    }
    
    $divs     = $this->findDivisions(array_keys($divIds));
    $fields   = $this->findFields   (array_keys($fieldIds));
    $regions  = $this->findRegions  (array_keys($regionIds));
    $projects = $this->findProjects (array_keys($projectIds));
    
    return array(
      'games'    => $this->index($games),
      'projects' => $projects,
      'persons'  => $persons,
      'teams'    => $teams,
      'fields'   => $fields,
      'regions'  => $regions,
      'divs'     => $divs,
    );
    
    //return $teams;
  }
  public function findTeams($teamIds)
  {
    if (count($teamIds) < 1) return array();
    
    $qb = $this->dbConn->createQueryBuilder();
    
    $qb->addSelect(array(
      'id          AS id',
      'name        AS name',
      'colors      AS colors',
      'coach       AS coachName',
      'coach_email AS coachEmail',
      'agegroup_id AS divId',
      'region_id   AS regionId',
    ));
    $qb->from('Team','team');
    //$qb->andWhere('team.id IN (' . $qb->createPositionalParameter($teamIds,DbConn::PARAM_INT_ARRAY) . ')');
    
    $qb->andWhere('team.id IN(?)');
    $sql = $qb->getSql();//die($sql);
    $teams = $this->dbConn->executeQuery($sql,array($teamIds),array(DbConn::PARAM_INT_ARRAY))->fetchAll();
    return $this->index($teams);
  }
  public function findProjects($projectIds)
  {
    if (count($projectIds) < 1) return array();
    
    $qb = $this->dbConn->createQueryBuilder();
    
    $qb->addSelect(array(
      'id         AS id',
      'name       AS name',
      'start_date AS dateStart',
      'end_date   AS dateEnd',
    ));
    $qb->from ('Project','project');
    $qb->where('project.id IN(?)');
    $sql = $qb->getSql();
    $projects = $this->dbConn->executeQuery($sql,array($projectIds),array(DbConn::PARAM_INT_ARRAY))->fetchAll();
    return $this->index($projects);
  }
  public function findPersons($personIds)
  {
    if (count($personIds) < 1) return array();
    
    $qb = $this->dbConn->createQueryBuilder();
    
    $qb->addSelect(array(
      'id           AS id',
      'first_name   AS nameFirst',
      'last_name    AS nameLast',
      'email        AS email',
      'phone_mobile AS phone',
      'ayso_id      AS aysoId',
      'ayso_my      AS aysoMemYear',
      'is_youth     AS isYouth',
      'region_id    AS regionId',
    ));
    $qb->from ('fos_user','person');
    $qb->where('person.id IN(?)');
    $sql = $qb->getSql();
    $persons = $this->dbConn->executeQuery($sql,array($personIds),array(DbConn::PARAM_INT_ARRAY))->fetchAll();
    return $this->index($persons);
  }
  public function findRegions($regionIds)
  {
    if (count($regionIds) < 1) return array();
    
    $qb = $this->dbConn->createQueryBuilder();
    
    $qb->addSelect(array(
      'id   AS id',
      'name AS name',
    ));
    $qb->from ('Region','region');
    $qb->where('region.id IN(?)');
    $sql = $qb->getSql();//die($sql);
    $regions = $this->dbConn->executeQuery($sql,array($regionIds),array(DbConn::PARAM_INT_ARRAY))->fetchAll();
    return $this->index($regions);
  }
  public function findDivisions($divIds)
  {
    if (count($divIds) < 1) return array();
    
    $qb = $this->dbConn->createQueryBuilder();
    
    $qb->addSelect(array(
      'id   AS id',
      'name AS name',
    ));
    $qb->from ('AgeGroup','divx');
    $qb->where('divx.id IN(?)');
    $sql = $qb->getSql();//die($sql);
    $divs = $this->dbConn->executeQuery($sql,array($divIds),array(DbConn::PARAM_INT_ARRAY))->fetchAll();
    return $this->index($divs);  
  }
  public function findFields($fieldIds)
  {
    if (count($fieldIds) < 1) return array();
    
    $qb = $this->dbConn->createQueryBuilder();
    
    $qb->addSelect(array(
      'id   AS id',
      'name AS name',
      'url  AS url',
    ));
    $qb->from ('Location','field');
    $qb->where('field.id IN(?)');
    $sql = $qb->getSql();//die($sql);
    $fields = $this->dbConn->executeQuery($sql,array($fieldIds),array(DbConn::PARAM_INT_ARRAY))->fetchAll();
    return $this->index($fields);
  }
  protected function index($items, $key = 'id')
  {
    $itemsx = array();
    foreach($items as $item) {
      $item[$key] = (integer)$item['id'];
      $itemsx[$item[$key]] = $item;
    }
    return $itemsx;
  }
  public function searchAction(Request $request, $projectId)
  {
    $this->verifyAccess($request);
    
    $projectId = (integer)$projectId;
    
    $dates = $request->query->get('dates');
    $date1 = $date2 = null;
    if ($dates)
    {
      $datesx = explode('-',$dates);
      if (isset($datesx[0])) $date1 = $datesx[0];
      if (isset($datesx[1])) $date2 = $datesx[1];
    }
    if (!$date1 & !$date2 & $projectId === 199)
    {
      
    }
    $data = $this->findProjectGames($projectId,$date1,$date2);
    
    $response = new JsonResponse($data);
    $response->headers->set('Access-Control-Allow-Origin',$request->headers->get('Origin'));
    return $response;
  }
  public function getAction(Request $request, $projectId, $gameNum)
  {
    $this->verifyAccess($request);
    
    $gameNum   = (integer)$gameNum;
    $projectId = (integer)$projectId;
    
    $data = $this->findProjectGame($projectId,$gameNum);
    
    $response = new JsonResponse($data);
    $response->headers->set('Access-Control-Allow-Origin',$request->headers->get('Origin'));
    return $response;
  }
}
