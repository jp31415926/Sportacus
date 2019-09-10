<?php

namespace Cerad\Component\Excel;

class ReaderFactory
{
  static function create($fileName,$readDataOnly = true)
  {
    // Most common case
    $reader = new \PHPExcel_Reader_Excel5();
        
    $reader->setReadDataOnly($readDataOnly);
        
    if ($reader->canRead($fileName)) return $reader;
 
    // Make sure have zip archive
    if (class_exists('ZipArchive'))
    {
      $reader = new \PHPExcel_Reader_Excel2007();
        
      $reader->setReadDataOnly($readDataOnly);
        
      if ($reader->canRead($fileName)) return $reader;
    }
        
    // Note that csv does not actually check for a csv file
    $reader = new \PHPExcel_Reader_CSV();
        
    if ($reader->canRead($fileName)) return $reader;

    throw new \Exception("No Reader found for $fileName");

  }
  // Returns Excel object
  public function load($fileName, $readDataOnly = true)
  {
    $reader = $this->createReaderForFile($fileName,$readDataOnly);

    return $reader->load($fileName);
  }
}
?>