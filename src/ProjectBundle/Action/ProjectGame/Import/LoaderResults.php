<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Import;

class LoaderResults
{
  public $filename;
  public $basename;
  public $worksheet;

  public $project;

  public $errors = [];
  public $games  = [];

  public function __toString()
  {
    ob_start();

    echo sprintf("Load Errors %d, Games Total %d\n",count($this->errors),count($this->games));

    if (count($this->errors)) {
      echo implode("\n",$this->errors);
      echo "\n";
    }

    return ob_get_clean();
  }
}