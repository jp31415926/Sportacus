<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Show;

use Psr\Http\Message\ServerRequestInterface as Request;

//  Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Cerad\Bundle\ProjectBundle\EntityRepository\ProjectGameRepositorySql;

use Cerad\Bundle\ProjectBundle\Action\ProjectGame\Show\ShowContentComponent;

class ShowController extends Controller
{
  protected $frameworkPrefix = 'sportacus';
  protected $projectGameRepository;
  protected $showContentComponent;

  public function __construct(
    $frameworkPrefix,
    ProjectGameRepositorySql $projectGameRepository,
    ShowContentComponent     $showContentComponent
  )
  {
    $this->frameworkPrefix = $frameworkPrefix;
    $this->projectGameRepository = $projectGameRepository;
    $this->showContentComponent  = $showContentComponent;
  }
  public function __invoke(Request $request, $gameId = 0)
  {
    $projectGame = [
      'id'     => 7878,
      'status' => 'Normal',
      'projectGameTeams' => [
        'away' => [
          'slot'   => 'away',
          'score'  => null,
          'source' => null,
          'projectTeam' => ['name' => 'AWAY Japan'],
        ],
        'home' => [
          'slot'   => 'home',
          'score'  => null,
          'source' => null,
          'projectTeam' => ['name' => 'HOME USA'],
        ],
      ]
    ];

    $projectGame = $this->projectGameRepository->findOne($gameId);

    $contentComponent = $this->showContentComponent;
    $contentComponent->setState(['projectGame' => $projectGame]);

    $tplData = [
      'contentComponent' => $contentComponent,
    ];
    return $this->render('@CeradProject/ProjectGame/Show/ShowContentTemplate.html.twig',$tplData);
  }
  public function showAction(Request $request, $id)
  {
    $projectGame = $this->projectGameRepository->find($id);
    if (!$projectGame) {
      throw $this->createNotFoundException(sprintf('Unable to find Game %d',$id));
    }
    $tplData = [
      'title'       => 'Show Game',
      'projectGame' => $projectGame,
    ];
    $tplName = $request->attributes->get('_tpl_name');
    return $this->render($tplName,$tplData);
  }
}