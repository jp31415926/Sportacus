<?php
namespace Cerad\ProjectLevel\Import;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends Command
{
  private $importer;

  public function __construct(ImporterExcel $importer)
  {
    $this->importer  = $importer;

    parent::__construct();
  }

  protected function configure()
  {
    $this->setName('cerad_project_level_import');

    $this->setDescription('Import project levels from spreadsheet.');

    $this->addArgument('file',InputArgument::REQUIRED,'File to import');

    $this->addOption('update', 'u', InputOption::VALUE_NONE,     'Update Database');
    $this->addOption('project','p', InputOption::VALUE_REQUIRED, 'Project ID',-1);
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $file      = $input->getArgument('file');
    $update    = $input->getOption('update') ? true : false;
    $projectId = $input->getOption('project');

    echo sprintf("Import Project Levels, Update: %d, Project: %d, File: %s\n",$update,$projectId,$file);

    $params = [
      'update'    => $update,
      'filename'  => $file,
      'basename'  => basename($file),
      'worksheet' => 'Levels',
    ];
    $results = $this->importer->import($params);

    echo $results;
  }
}
?>
