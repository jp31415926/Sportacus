<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Import;

use Psr\Http\Message\ServerRequestInterface as Request;

//  Symfony\Component\HttpFoundation\Request;
// Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Cerad\Bundle\ProjectBundle\EntityRepository\ProjectGameRepositorySql;

class ImportController extends Controller
{
  protected $saver;
  protected $loader;
  protected $gameRepository;

  public function __construct(ProjectGameRepositorySql $gameRepository, LoaderExcel $loader, SaverSql $saver)
  {
    $this->saver  = $saver;
    $this->loader = $loader;
    $this->gameRepository = $gameRepository;
  }
  public function __invoke(Request $request)
  {
    // Only for admins
    if (!$this->isGranted('ROLE_ADMIN')) {
      throw new AccessDeniedException();
    }
    $projectId = 20;

    $formData = [
      'project' => $projectId,
      'update'  => false,
      'file'    => null,
    ];
    $loaderResults = null;
    $saverResults  = null;

    $form = new ImportForm();
    $form->setData($formData);

    $form->handleRequest($request);

    if ($form->isValid()) {

      $formData = $form->getData();

      $file = $formData['file'];
      $filename = tempnam(sys_get_temp_dir(), 'PGI');
      $file->moveTo($filename);

      $params = [
        'projectId' => $formData['project'],
        'filename'  => $filename,
        'basename'  => $file->getClientFilename(),
        'worksheet' => null,
      ];
      $loaderResults = $this->loader->load($params);
      if ((count($loaderResults->errors) === 0) && $formData['update']) {
        $saverResults = $this->saver->save($loaderResults->games);
      }
    }
    $tplData = [
      'form' => $form,
      'loaderResults' => nl2br($loaderResults),
      'saverResults'  => nl2br($saverResults),
      'projectGameNumberMax' => $this->gameRepository->maxGameNumber($projectId),
    ];
    return $this->render('@CeradProject/ProjectGame/Import/ImportContentTemplate.html.twig',$tplData);
  }
}