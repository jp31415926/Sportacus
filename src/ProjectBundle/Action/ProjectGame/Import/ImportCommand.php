<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Import;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

class ImportCommand extends Command
{
  protected $saver;
  protected $loader;

  public function __construct(LoaderExcel $loader, SaverSql $saver)
  {
    $this->saver  = $saver;
    $this->loader = $loader;

    parent::__construct();
  }

  protected function configure()
  {
    $this->setName('cerad_project_game_import');

    $this->setDescription('Import Games, possibly update existing games');

    $this->addArgument('file',InputArgument::REQUIRED,'File to import');

    $this->addOption('update', 'u', InputOption::VALUE_NONE, 'Update Database');
    $this->addOption('project','p', InputOption::VALUE_REQUIRED, 'Project ID',-1);
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $file      = $input->getArgument('file');
    $update    = $input->getOption('update') ? true : false;
    $projectId = $input->getOption('project');

    echo sprintf("Import Games, Update: %d, Project: %d, File: %s\n",$update,$projectId,$file);

    $params = [
      'projectId' => $projectId,
      'filename'  => $file,
      'basename'  => basename($file),
      'worksheet' => null,
    ];
    $loaderResults = $this->loader->load($params);

    $games = $loaderResults->games;

    echo $loaderResults;

    file_put_contents($file . '.yml',Yaml::dump($games,10));

    if (count($loaderResults->errors)) return;

    if (!$update) return;

    $saverResults = $this->saver->save($games);

    echo $saverResults;
  }
}
?>
