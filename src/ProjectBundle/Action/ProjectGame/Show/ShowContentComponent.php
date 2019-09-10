<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Show;

use Cerad\Bundle\ProjectBundle\Action\ProjectGame\Twig\TwigExtension as Extension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ShowContentComponent
{
  private $state;

  protected function escape($string)
  {
    return htmlspecialchars($string, ENT_COMPAT | ENT_HTML5, 'UTF-8');
  }
  public function setState($state)
  {
    $this->state = $state;
  }
  protected $extension;
  protected $urlGenerator;
  protected $authorizationChecker;

  public function __construct(Extension $extension, UrlGeneratorInterface $urlGenerator, AuthorizationCheckerInterface $authorizationChecker)
  {
    $this->extension            = $extension;
    $this->urlGenerator         = $urlGenerator;
    $this->authorizationChecker = $authorizationChecker;
  }
  protected function renderProjectGameOfficials($projectGame)
  {
    $html = null;
    $projectGameOfficials = $this->extension->projectGameOfficialsSortedFilter($projectGame);
    foreach($projectGameOfficials as $projectGameOfficial) {
      $projectGameOfficialSlot = $this->extension->projectGameOfficialSlotFilter($projectGameOfficial);
      $projectGameOfficialName = $this->extension->projectGameOfficialNameFilter($projectGameOfficial);
      $html .= sprintf("<tr><th>%s</th><td>%s</td></tr>\n",
        $this->escape($projectGameOfficialSlot),
        $this->escape($projectGameOfficialName)
      );
    }
    return $html;
  }
  protected function renderProjectGameTeams($projectGame)
  {
    $html = null;
    $projectGameTeams = $this->extension->projectGameTeamsSortedFilter($projectGame);
    foreach($projectGameTeams as $projectGameTeam) {
      $projectGameTeamSlot = $this->extension->projectGameTeamSlotFilter($projectGameTeam);
      $projectGameTeamName = $this->extension->projectGameTeamNameFilter($projectGameTeam);
      $html .= sprintf("<tr><th>%s</th><td>%s</td></tr>\n",
        $this->escape($projectGameTeamSlot),
        $this->escape($projectGameTeamName)
      );
    }
    return $html;
  }
  protected function renderSchedulerLinks($projectGame)
  {
    if (!$this->authorizationChecker->isGranted('ROLE_SCHEDULER')) return null;

    $format = <<<TYPEOTHER
<a href="%s" class="btn btn-default"><i class="glyphicon glyphicon-%s"></i> %s</a>
TYPEOTHER;

    $id = $projectGame['id'];
    $html = null;
    $urlGenerator = $this->urlGenerator;

    $html .= sprintf($format . "\n", $urlGenerator->generate('game_edit', ['id' => $id]), 'edit',   'Edit');
    $html .= sprintf($format . "\n", $urlGenerator->generate('game_new'),                 'plus',   'New');
    $html .= sprintf($format . "\n", $urlGenerator->generate('game_clone',['id' => $id]), 'retweet','Clone');

    return $html;
  }
  public function render()
  {
    $projectGame = $this->state['projectGame'];

    $projectGameId       = $this->escape($projectGame['id']);
    $projectGameNumber   = $this->escape($projectGame['number']);
    $projectGameStatus   = $this->escape($this->extension->projectGameStatusFilter  ($projectGame));
    $projectGameDateTime = $this->escape($this->extension->projectGameDateTimeFilter($projectGame));
    $projectGameLength   = $this->escape($this->extension->projectGameLengthFilter  ($projectGame));

    $projectGameRegionLevel  = $this->escape($this->extension->projectGameRegionLevelFilter ($projectGame));
    $projectGameLocationName = $this->escape($this->extension->projectGameLocationNameFilter($projectGame));

    $projectGameScheduleRoute = $this->urlGenerator->generate('game') . '#project-game-' . $projectGameId;

    return <<<TYPEOTHER
<h1 id="content-title">Show Game #{$projectGameNumber}</h1>
<table class="record_properties table table-condensed">
<tbody>
<tr><th>ID</th><td>{$projectGameId}</td></tr>
<tr><th>Number</th><td>{$projectGameNumber}</td></tr>
<tr><th>Status</th><td>{$projectGameStatus}</td></tr>
{$this->renderProjectGameTeams($projectGame)}
<tr><th>Date Time</th><td>{$projectGameDateTime}</td></tr>
<tr><th>Length</th><td>{$projectGameLength}</td></tr>
<tr><th>Region Level</th><td>{$projectGameRegionLevel}</td></tr>
<tr><th>Location</th><td>{$projectGameLocationName}</td></tr>
{$this->renderProjectGameOfficials($projectGame)}
</tbody>
</table>
<br/>
<p><a href="{$projectGameScheduleRoute}" class="btn btn-default"><i class="glyphicon glyphicon-backward"></i> List</a></p>
<p>
{$this->renderSchedulerLinks($projectGame)}
</p>
TYPEOTHER;
  }
}
