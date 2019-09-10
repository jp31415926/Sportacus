<?php
namespace Cerad\Bundle\ProjectBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

use Cerad\Bundle\ProjectBundle\DependencyInjection\CeradProjectExtension;

class CeradProjectBundle extends Bundle
{
  public function getContainerExtension()
  {
    return new CeradProjectExtension();
  }
}