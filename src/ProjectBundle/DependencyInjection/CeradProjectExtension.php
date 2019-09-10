<?php
namespace Cerad\Bundle\ProjectBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class CeradProjectExtension extends Extension
{
  public function load(array $configs, ContainerBuilder $container)
  {
    $configuration = new Configuration();
    $config = $this->processConfiguration($configuration, $configs);

    // Useful for loading stuff from various directories
    $container->setParameter('sportacus_project_resources_dir',realpath(__DIR__ . '/../Resources'));
    $container->setParameter('sportacus_project_game_dir',     realpath(__DIR__ . '/../Action/ProjectGame'));

    $loader = new Loader\YamlFileLoader($container, new FileLocator(realpath(__DIR__ . '/../')));
    $loader->load('Resources/config/services.yml');
    $loader->load('Action/ProjectLevel/config/services.yml');
    $loader->load('Action/ProjectGame/config/services.yml');
  }
}
