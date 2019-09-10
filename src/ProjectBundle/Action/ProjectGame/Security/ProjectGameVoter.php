<?php
namespace Cerad\Bundle\ProjectBundle\Action\ProjectGame\Security;

use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Cerad\Bundle\ProjectBundle\Action\Project\Security\Role as ProjectRole;

class ProjectGameVoter implements VoterInterface
{
  const VIEW   = 'view';
  const EDIT   = 'edit';
  const CREATE = 'create';
  const UPDATE = 'update';
  const DELETE = 'delete';

  protected $hierarchy;

  public function __construct(RoleHierarchyInterface $hierarchy)
  {
    $this->hierarchy = $hierarchy;
  }

  protected function getProjectRole(array $projectRoles, $target)
  {
    $reachableRoles = $this->hierarchy->getReachableRoles($projectRoles);
    foreach($reachableRoles as $role)
    {
      if ($role->getRole() == $target) {
        foreach($projectRoles as $projectRole) {
          /** @noinspection PhpUndefinedMethodInspection */
          if ($projectRole->getRole() === $role->getRole()) return $projectRole;
        }
        // Matched against a parent role, return a project role with no restrictions
        return new ProjectRole($target);
      }
    }
    return null;
  }

  protected function canEdit(TokenInterface $token, $projectGame)
  {
    $projectRoles = $token->getRoles();

    $projectRole = $this->getProjectRole($projectRoles, 'ROLE_SCHEDULER');

    if (!$projectRole) return VoterInterface::ACCESS_DENIED;

    // require existence for now, add isset once it's stable
    $criteria = [
      'age' => isset($projectGame['level']['age']) ? $projectGame['level']['age'] : null,

      'project'      => $projectGame['project']['id'],
      'organization' => $projectGame['organization']['name'],
    ];
    if (!$projectRole->isGranted($criteria)) return VoterInterface::ACCESS_DENIED;

    return VoterInterface::ACCESS_GRANTED;
  }

  public function vote(TokenInterface $token, $projectGame, array $attrs)
  {
    if (!$this->supportsClass($projectGame)) return VoterInterface::ACCESS_ABSTAIN;

    $attr = $attrs[0];
    if (!$this->supportsAttribute($attr)) return VoterInterface::ACCESS_ABSTAIN;

    // Maybe add is published capability
    if ($attr === self::VIEW) return VoterInterface::ACCESS_GRANTED;

    return $this->canEdit($token,$projectGame);
  }

  public function supportsAttribute($attr)
  {
    switch($attr)
    {
      case self::VIEW:   return true;
      case self::EDIT:   return true;
      case self::CREATE: return true;
      case self::UPDATE: return true;
      case self::DELETE: return true;
    }
    return false;
  }

  // How to determine if an array is a project game?
  public function supportsClass($projectGame)
  {
    // instanceof is not working as expected
    if (is_object($projectGame)) {
      return get_class($projectGame) === 'Scheduler\SchBundle\Entity\Game' ? true : false;
    }
    if (is_array($projectGame)) {
      return isset($projectGame['typedef']) && $projectGame['typedef'] === 'project_game' ? true : false;
    }

    return false;
  }

}