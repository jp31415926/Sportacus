<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Export;

use Psr\Http\Message\ServerRequestInterface as Request;

use Zend\Diactoros\Stream;
use Zend\Diactoros\Response;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

// Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExportController extends Controller
{
  protected $exporter;

  public function __construct(ExporterExcel $exporter)
  {
    $this->exporter = $exporter;
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
    ];
    $form = new ExportForm();
    $form->setData($formData);

    $form->handleRequest($request);

    if ($form->isValid()) {
      $formData = $form->getData();

      $exporter = $this->exporter;

      $exporter->generate(['project' => $formData['project']]);

      $outFileName = 'Schedule' . date('Ymd-Hi') . '.' . $exporter->getFileExtension();

      $headers = [
        'Content-Type'        => $exporter->getContentType(),
        'Content-Disposition' => sprintf('attachment; filename="%s"',$outFileName),
      ];
      return new SymfonyResponse($exporter->getContents(),200,$headers);
    }
    $tplData = [
      'form' => $form,
    ];
    return $this->render('@CeradProject/ProjectGame/Export/ExportContentTemplate.html.twig',$tplData);
  }
}