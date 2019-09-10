<?php
namespace Cerad\Component\Excel;

class Loader
{
  protected $items = [];
    
  protected $record = [
    // 'region' => array('cols' => 'Region', 'req' => true, 'default' => 0),
  ];
  protected $map = array();

  protected function processDataRow($row)
  {
    $item = [];
    foreach($this->record as $name => $params) {
      $item[$name] = isset($params['default']) ? $params['default']: null;
    }
    foreach($row as $index => $value) {
      if (isset($this->map[$index])) {
        $name = $this->map[$index];
        $item[$name] = trim($value);
      }
    }
    return $item;
  }
  protected function processHeaderRow($row)
  {
    $errors = [];
    $found  = [];
    $record = $this->record;
    foreach($row as $index => $colName) {
      $colName = trim($colName);
      foreach($record as $name => $params) {
        $cols = is_array($params['cols']) ? $cols = $params['cols'] : [$cols = $params['cols']];
        foreach($cols as $col) {
          if ($col === $colName) {
            $plus = isset($params['plus']) ? $params['plus'] : 0;
            $this->map[$index + $plus] = $name;
            $found[$name] = true;
          }
        }
      }
    }
    // Make sure all required attributes found
    foreach($record as $name => $params) {
      if (isset($params['req']) && $params['req']) {
        if (!isset($found[$name])) {
          $cols = is_array($params['cols']) ? $cols = $params['cols'] : [$cols = $params['cols']];
          $cols = implode(' OR ',$cols);
          $errors[] = "ERR_HDR 1 Missing required header '$cols'";
        }
      }
    }
    return $errors;
  }
  // Leave as example for now
  public function loadItems($inputFileName, $worksheetName = null)
  {
    $reader = $this->excel->load($inputFileName);

    if ($worksheetName) $ws = $reader->getSheetByName($worksheetName);
    else                $ws = $reader->getSheet(0);
        
    $rows = $ws->toArray();
        
    $header = array_shift($rows);

    $this->processHeaderRow($header);

    foreach($rows as $row) {
      $item = $this->processDataRow($row);
            
      $this->processItem($item);
    }
    return $this->items;
  }
  /* ====================================================
   * Helper functions
   *
   */
  public function processTime($time)
  {
    return \PHPExcel_Style_NumberFormat::toFormattedString($time,'hh:mm:ss');
  }
  public function processDate($date)
  {
    return \PHPExcel_Style_NumberFormat::toFormattedString($date,'yyyy-MM-dd');
  }
  public function processDateTime($dt)
  {
    return \PHPExcel_Style_NumberFormat::toFormattedString($dt,'yyyy-MM-dd hh:mm:ss');
  }
  public function processDayOfWeek($date)
  {
    return \PHPExcel_Style_NumberFormat::toFormattedString($date,'D');
  }
}
