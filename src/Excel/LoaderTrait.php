<?php
namespace Cerad\Component\Excel;

trait LoaderTrait
{
  private function processDataRow($record,$map,$row)
  {
    $item = [];
    foreach($record as $name => $params) {
      $item[$name] = isset($params['default']) ? $params['default']: null;
    }
    foreach($row as $index => $value) {
      if (isset($map[$index])) {
        $name = $map[$index];
        $item[$name] = trim($value);
      }
    }
    return $item;
  }
  private function processHeaderRow($record,$row)
  {
    $map    = [];
    $errors = [];
    $found  = [];
    foreach($row as $index => $colName) {
      $colName = trim($colName);
      foreach($record as $name => $params) {
        $cols = is_array($params['cols']) ? $cols = $params['cols'] : [$cols = $params['cols']];
        foreach($cols as $col) {
          if ($col === $colName) {
            $plus = isset($params['plus']) ? $params['plus'] : 0;
            $map[$index + $plus] = $name;
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
          $errors[] = "Missing $cols";
        }
      }
    }
    return [$map,$errors];
  }
  /* ====================================================
   * Helper functions
   *
   */
  private function processTime($time)
  {
    return \PHPExcel_Style_NumberFormat::toFormattedString($time,'hh:mm:ss');
  }
  private function processDate($date)
  {
    return \PHPExcel_Style_NumberFormat::toFormattedString($date,'yyyy-MM-dd');
  }
  private function processDateTime($dt)
  {
    return \PHPExcel_Style_NumberFormat::toFormattedString($dt,'yyyy-MM-dd hh:mm:ss');
  }
  private function processDayOfWeek($date)
  {
    return \PHPExcel_Style_NumberFormat::toFormattedString($date,'D');
  }
}
