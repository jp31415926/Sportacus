<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Import;

use Doctrine\DBAL\Connection;

class LoaderRepository
{
  protected $dbConn;

  protected $games    = [];
  protected $teams    = [];
  protected $levels   = [];
  protected $fields   = [];
  protected $regions  = [];
  protected $projects = [];

  public function __construct(Connection $dbConn)
  {
    $this->dbConn = $dbConn;
  }
  public function findGameIdForDateTimeFieldSlot($date,$time,$fieldId)
  {
    $sql  = 'SELECT id FROM Game where date = ? AND time = ? AND location_id = ?';
    $stmt = $this->dbConn->executeQuery($sql,[$date,$time,$fieldId]);
    $rows = $stmt->fetchAll();
    return count($rows) === 0 ? null : $rows[0]['id'];
  }
  public function findTeam($projectId,$name)
  {
    // blank names are fine
    if (!$name) return null;

    $key = sprintf('%d %s',$projectId,$name);
    if (isset($this->teams[$key])) return $this->teams[$key];

    // "SELECT id FROM Team WHERE name='$team name' AND project_id=$project");
    $sql  = 'SELECT id, name FROM Team where project_id = ? AND name = ?';
    $stmt = $this->dbConn->executeQuery($sql,[$projectId,$name]);

    return $this->teams[$key] = $stmt->fetch();
  }
  public function findLevel($projectId,$regionName,$age)
  {
    $key = sprintf('%d %s %s',$projectId,$regionName,$age);

    if (isset($this->levels[$key])) return $this->levels[$key];

    // "SELECT id FROM AgeGroup WHERE name='$agegroup' AND project_id=$project")
    $sql = <<<TYPEOTHER
SELECT level.id, level.name

FROM AgeGroup AS level

LEFT JOIN Region AS region ON region.id = level.region_id

WHERE level.project_id = ? AND region.name = ? AND level.name = ?
TYPEOTHER;

    $stmt = $this->dbConn->executeQuery($sql,[$projectId,$regionName,$age]);

    return $this->levels[$key] = $stmt->fetch();
  }
  public function findRegion($name)
  {
    if (isset($this->regions[$name])) return $this->regions[$name];

    $sql  = 'SELECT id, name FROM Region where name = ?';
    $stmt = $this->dbConn->executeQuery($sql,[$name]);

    return $this->regions[$name] = $stmt->fetch();
  }
  public function findField($name)
  {
    if (isset($this->fields[$name])) return $this->fields[$name];

    $sql  = 'SELECT id, name FROM Location where name = ?';
    $stmt = $this->dbConn->executeQuery($sql,[$name]);

    return $this->fields[$name] = $stmt->fetch();
  }
  public function findProject($id)
  {
    if (isset($this->projects[$id])) return $this->projects[$id];

    //jp $sql  = 'SELECT id, name FROM Project where id = ?';
    $sql  = 'SELECT id, name, start_date, end_date FROM Project where id = ?';
    $stmt = $this->dbConn->executeQuery($sql,[$id]);

    return $this->projects[$id] = $stmt->fetch();
  }
}
