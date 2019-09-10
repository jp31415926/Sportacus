<?php
namespace Cerad\Bundle\ProjectBundle\Tests\Action\ProjectGame\Show;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Cerad\Bundle\ProjectBundle\Action\ProjectGame\Show\ShowContentComponent;
use Cerad\Bundle\ProjectBundle\Action\ProjectGame\Twig\TwigExtension as Extension;

class ShowControllerTest extends WebTestCase
{
  protected function assertEqualsEol($str1,$str2)
  {
    $str1 = str_replace(["\r","\n"],'',$str1);
    $str2 = str_replace(["\r","\n"],'',$str2);
    $this->assertEquals($str1,$str2);
  }
  public function testShowContentComponent()
  {
    $projectGame = [
      'id'          => 7878,
      'number'      => 103,
      'status'      => 'Normal',
      'date'        => '2015-06-20',
      'time'        => '15:30:00',
      'length'      => 80,
      'timeslotlength' => 105,
      'level'       => ['age'  => 'U19'],
      'region'      => ['name' => 'Area5C'],
      'location'    => ['name' => 'JH2'],

      'project_game_teams' => [
        'away' => [
          'slot'   => 'away',
          'score'  => null,
          'source' => null,
          'project_team' => ['name' => 'AWAY Japan'],
        ],
        'home' => [
          'slot'   => 'home',
          'score'  => null,
          'source' => null,
          'project_team' => ['name' => 'HOME USA'],
        ],
      ],
      'project_game_officials' => [
        'ref' => [
          'slot' => 'ref',
          'project_official' => ['name_first' => 'Les', 'name_last' => 'Daniel'],
        ],
        'ar2' => [
          'slot' => 'ar2',
          'project_official' => ['name_first' => 'Jim', 'name_last' => 'Burke'],
        ],
        'ar1' => [
          'slot' => 'ar1',
          'project_official' => ['name_first' => 'Bruce', 'name_last' => 'Beller'],
        ],
      ],
    ];
    $extension = new Extension();

    $urlGenerator = $this->prophesize('Symfony\Component\Routing\Generator\UrlGeneratorInterface');

    $urlGenerator->generate('game')->willReturn('/game/');

    $urlGenerator->generate('game_new')                 ->willReturn('/game/new');
    $urlGenerator->generate('game_edit', ['id' => 7878])->willReturn('/game/7878/edit');
    $urlGenerator->generate('game_clone',['id' => 7878])->willReturn('/game/7878/clone');

    $urlGenerator->generate('game')->shouldBeCalledTimes(1);

    $authoricationChecker = $this->prophesize('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
    $authoricationChecker->isGranted('ROLE_SCHEDULER')->willReturn(true);
    $authoricationChecker->isGranted('ROLE_SCHEDULER')->shouldBeCalledTimes(1);

    $component = new ShowContentComponent($extension,$urlGenerator->reveal(),$authoricationChecker->reveal());

    $component->setState(['projectGame' => $projectGame]);

    $expect = <<<TYPEOTHER
<h1 id="content-title">Show Game #103</h1>
<table class="record_properties table table-condensed">
<tbody>
<tr><th>ID</th><td>7878</td></tr>
<tr><th>Number</th><td>103</td></tr>
<tr><th>Status</th><td>Normal</td></tr>
<tr><th>Home Team</th><td>HOME USA</td></tr>
<tr><th>Away Team</th><td>AWAY Japan</td></tr>
<tr><th>Date Time</th><td>Sat Jun 20 2015 03:30 PM</td></tr>
<tr><th>Length</th><td>80/105 minutes</td></tr>
<tr><th>Region Level</th><td>Area5C U19</td></tr>
<tr><th>Location</th><td>JH2</td></tr>
<tr><th>Ref</th><td>Daniel, Les</td></tr>
<tr><th>AR1</th><td>Beller, Bruce</td></tr>
<tr><th>AR2</th><td>Burke, Jim</td></tr>
</tbody>
</table>
<br/>
<p><a href="/game/#project-game-7878" class="btn btn-default"><i class="glyphicon glyphicon-backward"></i> List</a></p>
<p>
<a href="/game/7878/edit" class="btn btn-default"><i class="glyphicon glyphicon-edit"></i> Edit</a>
<a href="/game/new" class="btn btn-default"><i class="glyphicon glyphicon-plus"></i> New</a>
<a href="/game/7878/clone" class="btn btn-default"><i class="glyphicon glyphicon-retweet"></i> Clone</a>
</p>
TYPEOTHER;

    $this->assertEqualsEol($expect,$component->render());
  }
  public function testShowController()
  {
    $client = static::createClient();

    $crawler = $client->request('GET', '/project-game/7878/show');

    //echo $client->getResponse()->getContent();

    $this->assertEquals(200,$client->getResponse()->getStatusCode());

    $contentTitleCrawler = $crawler->filter('#content-title');
    $this->assertEquals(1, $contentTitleCrawler->count());
    $this->assertEquals('Show Game #103',$contentTitleCrawler->text());
  }
}