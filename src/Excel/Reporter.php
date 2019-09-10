<?php
namespace Cerad\Component\Excel;
/* ========================================================================
 * Base class for exporting excel spreadsheets
 * 
 * Assume for now that Excell2007 is being written
 * 
 * Not all of the formatting stuff works properly under Excel5 aka 2003
 */

class Reporter
{
  protected function createSpreadSheet()
  {
    \PHPExcel_Cell::setValueBinder(new ValueBinder());
    return new \PHPExcel();
  }
  protected function createWriter($ss)
  {
    return \PHPExcel_IOFactory::createWriter($ss, 'Excel2007');
  }
  protected function writeHeaders($ws,$columns,$row,$headers)
  {
    $col = 0;
    foreach($headers as $key) {
      if (isset($columns[$key])) {

        $column = $columns[$key];

        $width = isset($column['width']) ? $column['width'] : 16;
        $title = isset($column['title']) ? $column['title'] : '';

        $ws->getColumnDimensionByColumn($col)->setWidth($width);

        $cell = $ws->getCellByColumnAndRow($col, $row);

        $cell->setValue($title);

        $this->justifyCell($column,'title_justify',$cell);
      }
      $col++; // Blank columns
    }
  }
  protected function writeValues($ws,$columns,$row,$values)
  {
    $col = 0;
    foreach ($values as $key => $value) {

      if (isset($columns[$key])) {
        $column = $columns[$key];

        $cell = $ws->getCellByColumnAndRow($col, $row);

        if (isset($column['type'])) {
          $value = [
            'data'   => $value,
            'type'   => $column['type'],
            'format' => isset($column['format']) ? $column['format'] : null,
          ];
        }
        $cell->setValue($value);

        $this->justifyCell($column,'value_justify',$cell);

      }
      $col++;
    }
  }
  /* ============================================================
   * Consider moving this into value binder as well
   *
   */
  protected function justifyCell($column,$key,$cell)
  {
    $justify = isset($column[$key]) ? $column[$key] : null;
    if (!$justify) return;

    $align = $cell->getStyle()->getAlignment();
    switch($justify) {
      case 'left' :
        $align->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        break;
      case 'right' :
        $align->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        break;
      case 'center' :
        $align->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        break;
    }
    return;
  }
  /* ===============================================
   * Add to value binder
   *
   */
  protected function transformPhoneNumber($phone)
  {
    if (!$phone) return null;
    return sprintf('%s.%s.%s',
      substr($phone,0,3),
      substr($phone,3,3),
      substr($phone,6)
    );
  }
  /* =======================================================
   * Called by controller to get the contents
   */
  protected $ss;

  public function getContents($ss = null)
  {
    if (!$ss) $ss = $this->ss;
    if (!$ss) return null;

    $objWriter = $this->createWriter($ss);

    ob_start();
    $objWriter->save('php://output');
    return ob_get_clean();
  }
  public function getFileExtension() 
  { 
    return 'xlsx';
  }
  public function getContentType() 
  { 
    return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'; 
  }
}
