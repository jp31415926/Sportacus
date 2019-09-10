<?php
namespace Cerad\Bundle\ProjectBundle\Tests\Action\ProjectGame\Twig;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Cerad\Bundle\ProjectBundle\Action\ProjectGame\Twig\TwigExtension;
use Cerad\Bundle\ProjectBundle\Action\ProjectGame\Twig\TwigExtension as Extension;

class ExtensionTest extends WebTestCase
{
  public function testProjectGameStatus()
  {
    $extension = new Extension();
    $projectGame = ['status' => 2];

    $status = $extension->projectGameStatusFilter($projectGame);
    $this->assertEquals('Complete',$status);
  }
  public function testProjectGameDateTime()
  {
    $extension = new TwigExtension();

    $projectGame = [
      'date' => '2015-07-13',
      'time' => '13:45:00',
    ];
    $dt = $extension->projectGameDateTimeFilter($projectGame);
    $this->assertEquals('Mon Jul 13 2015 01:45 PM',$dt);
  }
  public function testProjectGameLength()
  {
    $extension = new TwigExtension();

    $projectGame = [
      'length'      => 60,
      'timeslotlength' => 75,
    ];
    $length = $extension->projectGameLengthFilter($projectGame);
    $this->assertEquals('60/75 minutes',$length);
  }
  public function testProjectGameLocationName()
  {
    $extension = new Extension();

    $projectGame = [
      'location' => ['name' => 'JH2'],
    ];
    $name = $extension->projectGameLocationNameFilter($projectGame);
    $this->assertEquals('JH2',$name);
  }
  public function testProjectGameRegionLevel()
  {
    $extension = new Extension();

    $projectGame = [
      'region' => ['name' => 'Area5C'],
      'level'  => ['age'  => 'U19'],
    ];
    $value = $extension->projectGameRegionLevelFilter($projectGame);
    $this->assertEquals('Area5C U19',$value);
  }
  /* =================================================
   * ProjectGameTeams
   *
   */
  public function testProjectGameTeams()
  {
    $twigExtension = new TwigExtension();

    $projectGame = [
      'project_game_teams' => [
        'away' => ['slot' => 'away'],
        'home' => ['slot' => 'home'],
      ],
    ];
    $projectGameTeamsSorted = $twigExtension->projectGameTeamsSortedFilter($projectGame);

    $keys = array_keys($projectGameTeamsSorted);
    $this->assertEquals('home',$keys[0]);
    $this->assertEquals('away',$keys[1]);
  }
  public function testProjectGameTeamSlot()
  {
    $twigExtension = new TwigExtension();

    $projectGameTeam = [
      'slot' => 'away'
    ];
    $projectGameTeamSlot = $twigExtension->projectGameTeamSlotFilter($projectGameTeam);

    $this->assertEquals('Away Team',$projectGameTeamSlot);
  }
  public function tesProjectGameTeamName()
  {
    $twigExtension = new TwigExtension();

    $projectGameTeam = [
      'slot' => 'away',
      'source' => 'Win Game 999',
      'project_team' => null,
    ];
    $name = $twigExtension->projectGameTeamNameFilter($projectGameTeam);
    $this->assertEquals('Win Game 999',$name);

    $projectGameTeam['project_team'] = ['name' => 'Penguins'];
    $name = $twigExtension->projectGameTeamNameFilter($projectGameTeam);
    $this->assertEquals('Penguins',$name);

  }
  /* =================================================
   * ProjectGameOfficials
   *
   */
  public function testProjectGameOfficials()
  {
    $extension = new Extension();

    $projectGame = [
      'project_game_officials' => [
        'ar1' => ['slot' => 'ar1'],
        'ar2' => ['slot' => 'ar2'],
        'ref' => [
          'slot' => 'ref',
          'project_official' => [
            'name_first' => 'Hillary',
            'name_last'  => 'Clinton',
          ],
        ],
      ],
    ];
    $projectGameOfficialsSorted = $extension->projectGameOfficialsSortedFilter($projectGame);

    $keys = array_keys($projectGameOfficialsSorted);
    $this->assertEquals('ref',$keys[0]);
    $this->assertEquals('ar1',$keys[1]);
    $this->assertEquals('ar2',$keys[2]);

    $referee = $projectGameOfficialsSorted['ref'];

    $this->assertEquals('Ref',             $extension->projectGameOfficialSlotFilter($referee));
    $this->assertEquals('Clinton, Hillary',$extension->projectGameOfficialNameFilter($referee));
  }
}