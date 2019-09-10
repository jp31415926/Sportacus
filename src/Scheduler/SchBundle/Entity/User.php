<?php

namespace Scheduler\SchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\UserBundle\Model\User as BaseUser;

use Cerad\Bundle\ProjectBundle\Action\Project\Security\Role;
use Cerad\Bundle\ProjectBundle\Entity\ArrayAccessTrait;

/**
 * Scheduler\SchBundle\Entity\User
 *
 * @ORM\Table(name="fos_user")
 * @ORM\Entity(repositoryClass="Scheduler\SchBundle\Entity\UserRepository")
 * @UniqueEntity("ayso_id")
 * @UniqueEntity("phone_mobile")
 */
//class User extends BaseUser implements AdvancedUserInterface, \Serializable
class User extends BaseUser implements \ArrayAccess
{
  use ArrayAccessTrait;

  public function getRoles()
  {
    $roles = parent::getRoles();
    $projectRoles = [];
    foreach($roles as $role) {
      $projectRoles[] = new Role($role);
    }
    return $projectRoles;
  }
  public function hasRole($role)
  {
    $role = strtoupper($role);
    $role = is_string($role) ? new Role($role) : $role;

    $projectRoles = $this->getRoles();
    foreach($projectRoles as $projectRole) {
      if ($role->getRole() === $projectRole->getRole()) return true;
    }
    return false;
  }

  /**
   * @var integer $id
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $id;

  /**
   * @var string $first_name
   *
   * @ORM\Column(name="first_name", type="string", length=64)
   * @Assert\NotBlank()
   */
  private $first_name;

  /**
   * @var string $last_name
   *
   * @ORM\Column(name="last_name", type="string", length=64)
   * @Assert\NotBlank()
   */
  private $last_name;

  /**
   * @var string $phone_home
   *
   * @ORM\Column(name="phone_home", type="string", length=20, nullable=true)
   */
  private $phone_home;

  /**
   * @var string $phone_mobile
   *
   * @ORM\Column(name="phone_mobile", type="string", length=20, nullable=true, unique=true)
   */
  private $phone_mobile;

  /**
   * @var string $mobile_provider
   *
   * @ORM\ManyToOne(targetEntity="MobileProvider")
   */
  private $mobile_provider;

  /**
   * @var boolean $mobile_provider_verified ;
   *
   * @ORM\Column(name="mobile_provider_verified", type="boolean", nullable=true)
   */
  private $mobile_provider_verified;

  /**
   * @var string $ayso_id
   *
   * @ORM\Column(name="ayso_id", type="string", length=10, unique=true)
   * @Assert\Range(
   *   min = 10000000, minMessage = "AYSO ID must be a 8 or 9 digit number",
   *   max = 999999999, maxMessage = "AYSO ID must be a 8 or 9 digit number"
   * )
   */
  private $ayso_id;

  /**
   * @var string $ayso_my
   *
   * @ORM\Column(name="ayso_my", type="string", length=64, nullable=true)
   */
  private $ayso_my;

  /**
   * @var string $badge
   *
   * @ORM\Column(name="badge", type="string", length=64, nullable=true)
   */
  private $badge;

  /**
   * @var integer $project
   *
   * @ORM\ManyToOne(targetEntity="Project")
   * @ORM\JoinColumn(name="current_project_id", referencedColumnName="id")
   */
  private $current_project;

  /**
   * @var integer $region
   *
   * @ORM\ManyToOne(targetEntity="Region")
   * @ORM\JoinColumn(name="region_id", referencedColumnName="id")
   */
  private $region;

  /**
   * @var boolean $role_referee ;
   *
   * @ORM\Column(name="role_referee", type="boolean", nullable=true)
   */
  private $role_referee;

  /**
   * @var boolean $role_referee_admin ;
   *
   * @ORM\Column(name="role_referee_admin", type="boolean", nullable=true)
   */
  private $role_referee_admin;

  /**
   * @var boolean $role_scheduler ;
   *
   * @ORM\Column(name="role_scheduler", type="boolean", nullable=true)
   */
  private $role_scheduler;

  /**
   * @var boolean $role_assigner ;
   *
   * @ORM\Column(name="role_assigner", type="boolean", nullable=true)
   */
  private $role_assigner;

  /**
   * @var boolean $role_superuser ;
   *
   * @ORM\Column(name="role_superuser", type="boolean", nullable=true)
   */
  private $role_superuser;

  /**
   * @var boolean $is_youth ;
   *
   * @ORM\Column(name="is_youth", type="boolean", nullable=true)
   */
  private $is_youth;

  /**
   * @var boolean $option_change_email ;
   *
   * @ORM\Column(name="option_change_email", type="boolean", nullable=true)
   */
  private $option_change_email;

  /**
   * @var boolean $option_change_text ;
   *
   * @ORM\Column(name="option_change_text", type="boolean", nullable=true)
   */
  private $option_change_text;

  /**
   * @var boolean $option_reminder_email ;
   *
   * @ORM\Column(name="option_reminder_email", type="boolean", nullable=true)
   */
  private $option_reminder_email;

  /**
   * @var boolean $option_reminder_text ;
   *
   * @ORM\Column(name="option_reminder_text", type="boolean", nullable=true)
   */
  private $option_reminder_text;

  /**
   * @var boolean $option_assignment_email ;
   *
   * @ORM\Column(name="option_assignment_email", type="boolean", nullable=true)
   */
  private $option_assignment_email;

  /**
   * @var boolean $option_assignment_text ;
   *
   * @ORM\Column(name="option_assignment_text", type="boolean", nullable=true)
   */
  private $option_assignment_text;

  /**
   * @var boolean $option_assignment_email ;
   *
   * @ORM\Column(name="option_assigner_email", type="boolean", nullable=true)
   */
  private $option_assigner_email;

  /**
   * @var boolean $option_assignment_text ;
   *
   * @ORM\Column(name="option_assigner_text", type="boolean", nullable=true)
   */
  private $option_assigner_text;
  /**
   * @var \DateTime $created
   *
   * @ORM\Column(name="created", type="datetime")
   */
  private $created;

  /**
   * @var \DateTime $updated
   *
   * @ORM\Column(name="updated", type="datetime")
   */
  private $updated;

  /**
   *
   */
  public function __construct()
  {
    parent::__construct();
    $this->setRoleReferee(false);
    $this->setRoleRefereeAdmin(false);
    $this->setRoleScheduler(false);
    $this->setRoleAssigner(false);
    $this->setRoleSuperUser(false);
    $this->setMobileProviderVerified(false);
    $this->setCreated(new \DateTime());
    $this->setUpdated(new \DateTime());
    $this->option_change_email = true;
    $this->option_change_text = true;
    $this->option_reminder_email = true;
    $this->option_reminder_text = false;
    $this->option_assignment_email = true;
    $this->option_assignment_text = false;
    $this->option_assigner_email = true;
    $this->option_assigner_text = false;
  }

  /**
   * Get id
   *
   * @return integer
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set first_name
   *
   * @param string $first_name
   * @return User
   */
  public function setFirstName($first_name)
  {
    $this->first_name = $first_name;

    return $this;
  }

  /**
   * Get full name, first name, then last
   *
   * @return string
   */
  public function getFullName()
  {
    $youth = $this->is_youth ? ' (y)' : '';
    return $this->first_name . ' ' . $this->last_name . $youth;
  }

  /**
   * Get full name, first name, then last
   *
   * @return string
   */
  public function getFullNameAndRegion()
  {
    $name = $this->getFullName();
    $name .= '-' . $this->region->getName();
    return $name;
  }

  /**
   * Get full name, first name, then last
   *
   * @return string
   */
  public function getFullNameAndBadge()
  {
    $name = $this->getFullName();
    /*
    $fn = '';
    switch (strtolower($this->badge)) {
    case 'regional':
      $fn = 'r.png'; break;
    case 'intermediate':
      $fn = 'i.png'; break;
    case 'advanced':
      $fn = 'a.png'; break;
    case 'national':
      $fn = 'i.png'; break;
    }
    if (!empty($fn)) {
      $name .= ' <img src="/assets/img/'.$fn.'" title="'.$this->badge.'">';
    }
     */
    return $name;
  }

  /**
   * Get full name, last name, then first
   *
   * @return string
   */
  public function getLastNameFirstName()
  {
    $youth = $this->is_youth ? ' (y)' : '';
    return $this->last_name . ', ' . $this->first_name . $youth;
  }

  /**
   * Get first_name
   *
   * @return string
   */
  public function getFirstName()
  {
    return $this->first_name;
  }

  /**
   * Set last_name
   *
   * @param string $last_name
   * @return User
   */
  public function setLastName($last_name)
  {
    $this->last_name = $last_name;

    return $this;
  }

  /**
   * Get last_name
   *
   * @return string
   */
  public function getLastName()
  {
    return $this->last_name;
  }

  /**
   * Set created
   *
   * @param \DateTime $created
   * @return User
   */
  public function setCreated($created)
  {
    $this->created = $created;

    return $this;
  }

  /**
   * Get created
   *
   * @return \DateTime
   */
  public function getCreated()
  {
    return $this->created;
  }

  /**
   * Set updated
   *
   * @param \DateTime $updated
   * @return User
   */
  public function setUpdated($updated)
  {
    $this->updated = $updated;

    return $this;
  }

  /**
   * Get updated
   *
   * @return \DateTime
   */
  public function getUpdated()
  {
    return $this->updated;
  }


  /**
   * @return string
   */
  public function __toString()
  {
    return $this->getLastNameFirstName();
    //return $this->first_name . ' ' . $this->last_name;
  }


  /**
   * Set phone_home
   *
   * @param string $phone_home
   * @return User
   */
  public function setPhoneHome($phone_home)
  {
    // delete all characters except digits
    $phone_home = preg_replace('/[^0-9]/', '', $phone_home);
    if (empty($phone_home))
      $phone_home = NULL;
    $this->phone_home = $phone_home;

    return $this;
  }

  /**
   * Get phone_home
   *
   * @return string
   */
  public function getPhoneHome()
  {
    return $this->phone_home;
  }

  /**
   * Set phone_mobile
   *
   * @param string $phone_mobile
   * @return User
   */
  public function setPhoneMobile($phone_mobile)
  {
    // delete all characters except digits
    $phone_mobile = preg_replace('/[^0-9]/', '', $phone_mobile);
    if (empty($phone_mobile))
      $phone_mobile = NULL;
    $this->phone_mobile = $phone_mobile;

    return $this;
  }

  /**
   * Get phone_mobile
   *
   * @return string
   */
  public function getPhoneMobile()
  {
    return $this->phone_mobile;
  }

  /**
   * Set ayso_id
   *
   * @param string $aysoId
   * @return User
   */
  public function setAysoId($aysoId)
  {
    $this->ayso_id = $aysoId;

    return $this;
  }

  /**
   * Get ayso_id
   *
   * @return string
   */
  public function getAysoId()
  {
    return $this->ayso_id;
  }

  /**
   * Set role_referee
   *
   * @param boolean $roleReferee
   * @return User
   */
  public function setRoleReferee($roleReferee)
  {
    $this->role_referee = $roleReferee;

    return $this;
  }

  /**
   * Get role_referee
   *
   * @return boolean
   */
  public function getRoleReferee()
  {
    return $this->role_referee;
  }

  /**
   * Set role_scheduler
   *
   * @param boolean $roleScheduler
   * @return User
   */
  public function setRoleScheduler($roleScheduler)
  {
    $this->role_scheduler = $roleScheduler;

    return $this;
  }

  /**
   * Get role_scheduler
   *
   * @return boolean
   */
  public function getRoleScheduler()
  {
    return $this->role_scheduler;
  }

  /**
   * Set mobile_provider
   *
   * @param MobileProvider $mobileProvider
   * @return User
   */
  public function setMobileProvider(MobileProvider $mobileProvider = null)
  {
    $this->mobile_provider = $mobileProvider;

    return $this;
  }

  /**
   * Get mobile_provider
   *
   * @return MobileProvider
   */
  public function getMobileProvider()
  {
    return $this->mobile_provider;
  }

  /**
   * Set mobile_provider_verified
   *
   * @param boolean $mobileProviderVerified
   * @return User
   */
  public function setMobileProviderVerified($mobileProviderVerified)
  {
    $this->mobile_provider_verified = $mobileProviderVerified;

    return $this;
  }

  /**
   * Get mobile_provider_verified
   *
   * @return boolean
   */
  public function getMobileProviderVerified()
  {
    return $this->mobile_provider_verified;
  }

  /**
   * Set region
   *
   * @param Region $region
   * @return User
   */
  public function setRegion(Region $region = null)
  {
    $this->region = $region;

    return $this;
  }

  /**
   * Get region
   *
   * @return Region
   */
  public function getRegion()
  {
    return $this->region;
  }

  /**
   * Set option_change_email
   *
   * @param boolean $optionChangeEmail
   * @return User
   */
  public function setOptionChangeEmail($optionChangeEmail)
  {
    $this->option_change_email = $optionChangeEmail;

    return $this;
  }

  /**
   * Get option_change_email
   *
   * @return boolean
   */
  public function getOptionChangeEmail()
  {
    return $this->option_change_email;
  }

  /**
   * Set option_change_text
   *
   * @param boolean $optionChangeText
   * @return User
   */
  public function setOptionChangeText($optionChangeText)
  {
    $this->option_change_text = $optionChangeText;

    return $this;
  }

  /**
   * Get option_change_text
   *
   * @return boolean
   */
  public function getOptionChangeText()
  {
    return $this->option_change_text;
  }

  /**
   * Set option_reminder_email
   *
   * @param boolean $optionReminderEmail
   * @return User
   */
  public function setOptionReminderEmail($optionReminderEmail)
  {
    $this->option_reminder_email = $optionReminderEmail;

    return $this;
  }

  /**
   * Get option_reminder_email
   *
   * @return boolean
   */
  public function getOptionReminderEmail()
  {
    return $this->option_reminder_email;
  }

  /**
   * Set option_reminder_text
   *
   * @param boolean $optionReminderText
   * @return User
   */
  public function setOptionReminderText($optionReminderText)
  {
    $this->option_reminder_text = $optionReminderText;

    return $this;
  }

  /**
   * Get option_reminder_text
   *
   * @return boolean
   */
  public function getOptionReminderText()
  {
    return $this->option_reminder_text;
  }

  /**
   * Set option_assignment_email
   *
   * @param boolean $optionAssignmentEmail
   * @return User
   */
  public function setOptionAssignmentEmail($optionAssignmentEmail)
  {
    $this->option_assignment_email = $optionAssignmentEmail;

    return $this;
  }

  /**
   * Get option_assignment_email
   *
   * @return boolean
   */
  public function getOptionAssignmentEmail()
  {
    return $this->option_assignment_email;
  }

  /**
   * Set option_assignment_text
   *
   * @param boolean $optionAssignmentText
   * @return User
   */
  public function setOptionAssignmentText($optionAssignmentText)
  {
    $this->option_assignment_text = $optionAssignmentText;

    return $this;
  }

  /**
   * Get option_assignment_text
   *
   * @return boolean
   */
  public function getOptionAssignmentText()
  {
    return $this->option_assignment_text;
  }

  /**
   * Set option_assigner_email
   *
   * @param boolean $optionAssignerEmail
   * @return User
   */
  public function setOptionAssignerEmail($optionAssignerEmail)
  {
    $this->option_assigner_email = $optionAssignerEmail;

    return $this;
  }

  /**
   * Get option_assigner_email
   *
   * @return boolean
   */
  public function getOptionAssignerEmail()
  {
    return $this->option_assigner_email;
  }

  /**
   * Set option_assigner_text
   *
   * @param boolean $optionAssignerText
   * @return User
   */
  public function setOptionAssignerText($optionAssignerText)
  {
    $this->option_assigner_text = $optionAssignerText;

    return $this;
  }

  /**
   * Get option_assigner_text
   *
   * @return boolean
   */
  public function getOptionAssignerText()
  {
    return $this->option_assigner_text;
  }

  /**
   * Set ayso_my
   *
   * @param string $aysoMy
   * @return User
   */
  public function setAysoMy($aysoMy)
  {
    $this->ayso_my = $aysoMy;

    return $this;
  }

  /**
   * Get ayso_my
   *
   * @return string
   */
  public function getAysoMy()
  {
    return $this->ayso_my;
  }

  /**
   * Set badge
   *
   * @param string $badge
   * @return User
   */
  public function setBadge($badge)
  {
    $this->badge = $badge;

    return $this;
  }

  /**
   * Get badge
   *
   * @return string
   */
  public function getBadge()
  {
    return $this->badge;
  }

  /**
   * Set current_project
   *
   * @param Project $currentProject
   * @return User
   */
  public function setCurrentProject(Project $currentProject = null)
  {
    $this->current_project = $currentProject;

    return $this;
  }

  /**
   * Get current_project
   *
   * @return Project
   */
  public function getCurrentProject()
  {
    return $this->current_project;
  }

  /**
   * Set role_referee_admin
   *
   * @param boolean $roleRefereeAdmin
   * @return User
   */
  public function setRoleRefereeAdmin($roleRefereeAdmin)
  {
    $this->role_referee_admin = $roleRefereeAdmin;

    return $this;
  }

  /**
   * Get role_referee_admin
   *
   * @return boolean
   */
  public function getRoleRefereeAdmin()
  {
    return $this->role_referee_admin;
  }

  /**
   * Set is_youth
   *
   * @param boolean $isYouth
   * @return User
   */
  public function setIsYouth($isYouth)
  {
    $this->is_youth = $isYouth;

    return $this;
  }

  /**
   * Get is_youth
   *
   * @return boolean
   */
  public function getIsYouth()
  {
    return $this->is_youth;
  }

  /**
   * Set role_assigner
   *
   * @param boolean $roleAssigner
   * @return User
   */
  public function setRoleAssigner($roleAssigner)
  {
    $this->role_assigner = $roleAssigner;

    return $this;
  }

  /**
   * Get role_assigner
   *
   * @return boolean
   */
  public function getRoleAssigner()
  {
    return $this->role_assigner;
  }

  /**
   * Set role_superuser
   *
   * @param boolean $roleSuperuser
   * @return User
   */
  public function setRoleSuperuser($roleSuperuser)
  {
    $this->role_superuser = $roleSuperuser;

    return $this;
  }

  /**
   * Get role_superuser
   *
   * @return boolean
   */
  public function getRoleSuperuser()
  {
    return $this->role_superuser;
  }
}
