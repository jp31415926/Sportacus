<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Export;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCommand extends Command
{
  protected $dataDir;
  protected $exporter;

  public function __construct(ExporterExcel $exporter)
  {
    $this->dataDir  = '../var/schedules';
    $this->exporter = $exporter;

    parent::__construct();
  }
  protected function configure()
  {
    $this->setName('cerad_project_game_export');

    $this->setDescription('Export Game Schedule');

    $this->addOption('project','p', InputOption::VALUE_REQUIRED, 'Project ID',-1);
  }
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    //jp don't create directory in constructor since we don't always need it.
    if (!is_dir($this->dataDir)) {
      @mkdir($this->dataDir, 0755, true);
    }
    $projectId = $input->getOption('project');

    $this->exporter->generate(['project' => $projectId]);

    $fileName = sprintf('%s/Schedule%02d.xlsx',$this->dataDir,$projectId);
    file_put_contents($fileName, $this->exporter->getContents());

    echo sprintf("Generated Game Schedule %s\n",$fileName);
  }
}
?>
