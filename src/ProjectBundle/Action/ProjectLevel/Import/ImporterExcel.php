<?php

namespace Cerad\ProjectLevel\Import;

use Cerad\Component\Excel\LoaderTrait;

use Doctrine\DBAL\Connection;

final class ImporterExcel // extends Loader
{
  use LoaderTrait;

  private $dbConn;
  private $update = false;

  public function __construct(Connection $dbConn)
  {
    $this->dbConn = $dbConn;
  }
  private $record = [
    'project_id' => ['cols' => ['ProjectID'],'req' => true,  'default' => null],
    'id'         => ['cols' => ['LevelID'],  'req' => false, 'default' => null],
    'name'       => ['cols' => ['Name'],     'req' => true,  'default' => null],
    'title'      => ['cols' => ['Title'],    'req' => false, 'default' => null],
    'age'        => ['cols' => ['Age'],      'req' => false, 'default' => null],
    'gender'     => ['cols' => ['Gender'],   'req' => false, 'default' => null],
    'division'   => ['cols' => ['Division'], 'req' => false, 'default' => null],
    'level_key'  => ['cols' => ['LevelKey'], 'req' => false, 'default' => null],
    'crew_type'  => ['cols' => ['Crew'],     'req' => false, 'default' => null],

    'game_slot_length' => ['cols' => ['Length'], 'req' => false, 'default' => null],
  ];

  private function processItem($results,$item)
  {
    // Need project and name
    if (!isset($item['project_id']) || !isset($item['name'])) return;
    $results->total++;

    // Check for delete
    $id = (integer)$item['id'];
    if ($id < 0) {
      $id *= -1;
      $sql = 'SELECT id FROM project_levels WHERE id = ?;';
      $rows = $this->dbConn->executeQuery($sql,[$id])->fetchAll();
      if (count($rows) === 1) {
        $results->deleted++;
        if ($this->update) {
          $this->dbConn->delete('project_levels',['id' => $id]);
        }
        return;
      }
      // Already deleted, just ignore
      return;
    }
    // Don't use id anymore
    unset($item['id']);

    // Check for existing
    $sql = 'SELECT * FROM project_levels WHERE project_id = ? AND name = ?;';
    $stmt = $this->dbConn->executeQuery($sql,[$item['project_id'],$item['name']]);
    $rows = $stmt->fetchAll();
    if (count($rows) === 0) {
      if ($this->update) {
        $this->dbConn->insert('project_levels', $item);
      }
      $results->created++;
      return;
    }
    // Check for updates
    $row = $rows[0];
    $updates = [];
    foreach(array_keys($item) as $key) {
      if ($row[$key] !== $item[$key]) {
        $updates[$key] = $item[$key];
      }
    }
    if (count($updates) === 0) return;

    $results->updated++;
    if ($this->update) {
      $this->dbConn->update('project_levels', $updates, ['id' => $row['id']]);
    }
  }
  /* =========================================================
   * Main entry point
   *
   */
  public function import(array $params)
  {
    $this->update = $params['update'];

    // Results
    $results = new ImporterResults();
    $results->filename  = $filename  = $params['filename'];
    $results->basename  = $basename  = $params['basename'];
    $results->worksheet = $worksheet = $params['worksheet'];

    /** @var \PHPExcel_Reader_Abstract $reader */
    $reader = \PHPExcel_IOFactory::createReaderForFile($filename);
    $reader->setReadDataOnly(true);

    $wb = $reader->load($filename);
    $ws = $wb->getSheetByName($worksheet);

    $rows = $ws->toArray();

    $headerRow = array_shift($rows);

    $record = $this->record;
    $map = $this->processHeaderRow($record,$headerRow);
    if (count($map[1])) {
      $results->errors = $map[1];
      return $results;
    }
    $map = $map[0];

    foreach($rows as $row) {
      $item = $this->processDataRow($record,$map,$row);
      $this->processItem($results,$item);
    }
    return $results;
  }
}
