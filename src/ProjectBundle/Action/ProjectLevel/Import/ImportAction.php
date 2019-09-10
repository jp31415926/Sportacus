<?php
namespace Cerad\ProjectLevel\Import;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ImportAction extends Controller
{
  protected $importer;

  public function __construct(ImporterExcel $importer)
  {
    $this->importer  = $importer;
  }
  public function __invoke(RequestInterface $request)
  {
    // Only for admins
    if (!$this->isGranted('ROLE_ADMIN')) {
      throw new AccessDeniedException();
    }
    $resultsStr = null;

    $formData = [
      'update'  => false,
      'file'    => null,
    ];
    $form = new ImportForm();
    $form->setData($formData);

    $form->handleRequest($request);

    if ($form->isValid()) {

      $formData = $form->getData();

      /** @var UploadedFileInterface $file */
      $file = $formData['file'];
      $filename = tempnam(sys_get_temp_dir(), 'PLI');
      $file->moveTo($filename);

      $params = [
        'filename'  => $filename,
        'basename'  => $file->getClientFilename(),
        'worksheet' => 'Levels',
        'update'    => $formData['update'],
      ];
      $results = $this->importer->import($params);
      $resultsStr = $results->__toString();
    }
    $tplData = [
      'form' => $form,
      'results' => nl2br($resultsStr),
    ];
    return $this->render('@CeradProject/ProjectLevel/Import/ImportContent.html.twig',$tplData);
  }
}