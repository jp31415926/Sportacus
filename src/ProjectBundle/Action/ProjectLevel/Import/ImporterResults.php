<?php
namespace Cerad\ProjectLevel\Import;

class ImporterResults
{
  public $filename;
  public $basename;
  public $worksheet;

  public $project;

  public $errors = [];
  public $levels = [];

  public $total   = 0;
  public $created = 0;
  public $updated = 0;
  public $deleted = 0;

  public function __toString()
  {
    ob_start();

    echo sprintf("File: %s, Load Errors %d, Levels Total %d, Created %d, Updated %d, Deleted %d\n",
      $this->basename,
      count($this->errors),
      $this->total,
      $this->created,$this->updated,$this->deleted
    );

    if (count($this->errors)) {
      echo implode("\n",$this->errors);
      echo "\n";
    }

    return ob_get_clean();
  }
}