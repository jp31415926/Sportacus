<?php
namespace Cerad\Bundle\ProjectBundle\Tests\ProjectGame\Security;

use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken as Token;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

use Cerad\Bundle\ProjectBundle\Action\Project\Security\Role as ProjectRole;
use Cerad\Bundle\ProjectBundle\Action\ProjectGame\Security\ProjectGameVoter;

use Scheduler\SchBundle\Entity\Game;
use Scheduler\SchBundle\Entity\Team;
use Scheduler\SchBundle\Entity\Region as Org;
use Scheduler\SchBundle\Entity\Project;

class ProjectGameVoterTest extends \PHPUnit_Framework_TestCase
{
  public function test1()
  {
    $hierarchy = new RoleHierarchy([
      'ROLE_ADMIN' => ['ROLE_SCHEDULER']
    ]);

    $game = [
      'typedef' => 'project_game',
      'project' => ['id' => 18],
      'organization' => ['name' => 'R1174'],
    ];

    $voter = new ProjectGameVoter($hierarchy);

    $token = new Token(null,'user',[new ProjectRole('ROLE_PUBLIC')]);
    $vote = $voter->vote($token,$game,['view']);
    $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);

    $token = new Token(null,'user',[new ProjectRole('ROLE_SCHEDULER')]);
    $vote = $voter->vote($token,$game,['edit']);
    $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);

    $token = new Token(null,'user',[new ProjectRole('ROLE_REF')]);
    $vote = $voter->vote($token,$game,['edit']);
    $this->assertEquals(VoterInterface::ACCESS_DENIED, $vote);

    $token = new Token(null,'user',[new ProjectRole('ROLE_ADMIN')]);
    $vote = $voter->vote($token,$game,['edit']);
    $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);
  }

  public function testProject()
  {
    $hierarchy = new RoleHierarchy([
      'ROLE_ADMIN' => ['ROLE_SCHEDULER']
    ]);

    $voter = new ProjectGameVoter($hierarchy);

    $game = [
      'typedef' => 'project_game',
      'project' => ['id' => 18],
      'organization' => ['name' => 'R1174'],
    ];
    $token = new Token(null, 'user', [new ProjectRole('ROLE_SCHEDULER:P19')]);
    $vote = $voter->vote($token, $game, ['edit']);
    $this->assertEquals(VoterInterface::ACCESS_DENIED, $vote);

    $game = [
      'typedef' => 'project_game',
      'project' => ['id' => 19],
      'organization' => ['name' => 'R1174'],
    ];
    $vote = $voter->vote($token, $game, ['edit']);
    $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);
  }
  public function testSupportsClass()
  {
    $hierarchy = new RoleHierarchy([
      'ROLE_ADMIN' => ['ROLE_SCHEDULER']
    ]);

    $voter = new ProjectGameVoter($hierarchy);
    $token = new Token(null, 'user', [new ProjectRole('ROLE_SCHEDULER:P19')]);

    $game = [
      'typedef' => 'project_team',
      'project' => ['id' => 18],
      'organization' => ['name' => 'R1174'],
    ];
    $vote = $voter->vote($token, $game, ['edit']);
    $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $vote);

    $game = [
      'project' => ['id' => 18],
      'organization' => ['name' => 'R1174'],
    ];
    $vote = $voter->vote($token, $game, ['edit']);
    $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $vote);

    $team = new Team();
    $vote = $voter->vote($token, $team, ['edit']);
    $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $vote);

    $project  = new Project(); $project['id'] = 19;
    $orgR0160 = new Org(); $orgR0160['name'] = 'R0160';

    $game = new Game();
    $game['project'] = $project;
    $game['organization'] = $orgR0160;

    $vote = $voter->vote($token, $game, ['edit']);
    $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);
  }
  public function testProjectOrganization()
  {
    $hierarchy = new RoleHierarchy([
      'ROLE_ADMIN' => ['ROLE_SCHEDULER']
    ]);

    $voter = new ProjectGameVoter($hierarchy);

    $tokenR0160 = new Token(null, 'user', [new ProjectRole('ROLE_SCHEDULER:P19:OR0160')]);
    $tokenR0498 = new Token(null, 'user', [new ProjectRole('ROLE_SCHEDULER:P19:OR0498')]);

    $project19 = new Project(); $project19['id'] = 19;
    $project33 = new Project(); $project33['id'] = 33;

    $orgR0160 = new Org(); $orgR0160['name'] = 'R0160';
    $orgR0498 = new Org(); $orgR0498['name'] = 'R0498';

    $game = new Game();
    $game['project'] = $project19;

    $game['organization'] = $orgR0160;
    $vote = $voter->vote($tokenR0160, $game, ['edit']);
    $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);

    $game['organization'] = $orgR0498;
    $vote = $voter->vote($tokenR0160, $game, ['edit']);
    $this->assertEquals(VoterInterface::ACCESS_DENIED, $vote);

    $vote = $voter->vote($tokenR0498, $game, ['edit']);
    $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);

  }
}