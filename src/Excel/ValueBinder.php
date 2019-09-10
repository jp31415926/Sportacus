<?php
namespace Cerad\Component\Excel;

class ValueBinder extends \PHPExcel_Cell_DefaultValueBinder
{
  public function bindValue(\PHPExcel_Cell $cell, $value = null)
  {
    // Allow for custom formats by passing in arrays for values
    if (!is_array($value)) return parent::bindValue($cell,$value);

    $data = $value['data'];

    switch($value['type']) {

      case 'dow':

        $dateObj = is_object($data) ? $data : \DateTime::createFromFormat('Y-m-d',$data);
        return parent::bindValue($cell,$dateObj->format('D'));

      case 'date':

        $dateStr = is_object($data) ? $data->format('Y-m-d') : $data;

        $dateNum = \PHPExcel_Shared_Date::stringToExcel($dateStr);

        $cell->setValueExplicit($dateNum, \PHPExcel_Cell_DataType::TYPE_NUMERIC);

        $formatCode = isset($value['format']) ? $value['format'] : 'yyyy-mm-dd';

        $cell->getStyle()->getNumberFormat()->setFormatCode($formatCode);

        return true;

      case 'time':

        $timeStr = is_object($data) ? $data->format('H:i') : $data;

        list($h, $m) = explode(':', $timeStr);
        $timeNum = $h / 24 + $m / 1440;

        $cell->setValueExplicit($timeNum, \PHPExcel_Cell_DataType::TYPE_NUMERIC);

        $formatCode = isset($value['format']) ? $value['format'] : 'h:mm AM/PM';

        $cell->getStyle()->getNumberFormat()->setFormatCode($formatCode);

        return true;

      case 'phone':

        $phoneNum = $data;

        if (!$phoneNum)
        {
          return true;
        }
        $cell->setValueExplicit($phoneNum, \PHPExcel_Cell_DataType::TYPE_NUMERIC);

        $formatCode = isset($value['format']) ? $value['format'] : '(###) ###-####';

        $cell->getStyle()->getNumberFormat()->setFormatCode($formatCode);

        return true;
    }
    return parent::bindValue($cell,$value);
  }
}