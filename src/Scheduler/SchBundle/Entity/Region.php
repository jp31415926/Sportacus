<?php

namespace Scheduler\SchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Cerad\Bundle\ProjectBundle\Entity\ProjectOrganizationTrait;

/**
 * Region
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Scheduler\SchBundle\Entity\RegionRepository")
 */
class Region implements \ArrayAccess
{
  use ProjectOrganizationTrait;

  /**
   * @var integer
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var string
   *
   * @ORM\Column(name="name", type="string", length=32)
   */
  private $name;

  /**
   * @var string
   *
   * @ORM\Column(name="long_name", type="string", length=64)
   */
  private $long_name;

  /**
   * @var string
   *
   * @ORM\Column(name="poc_name", type="string", length=255, nullable=true)
   */
  private $poc_name;

  /**
   * @var string
   *
   * @ORM\Column(name="poc_email", type="string", length=255, nullable=true)
   * @Assert\Email(
   *     message = "The email '{{ value }}' is not a valid email address.",
   *     checkMX = true
   * )
   */
  private $poc_email;

  /**
   * @var string
   *
   * @ORM\Column(name="ref_admin_name", type="string", length=255, nullable=true)
   */
  private $ref_admin_name;

  /**
   * @var string
   *
   * @ORM\Column(name="ref_admin_email", type="string", length=255, nullable=true)
   * @Assert\Email(
   *     message = "The email '{{ value }}' is not a valid email address.",
   *     checkMX = true
   * )
   */
  private $ref_admin_email;


  /**
   * Get string representation of class
   *
   * @return string
   */
  public function __toString()
  {
    return $this->getLongName();
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
   * Set name
   *
   * @param string $name
   * @return Region
   */
  public function setName($name)
  {
    $this->name = $name;

    return $this;
  }

  /**
   * Get name
   *
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set long_name
   *
   * @param string $longName
   * @return Region
   */
  public function setLongName($longName)
  {
    $this->long_name = $longName;

    return $this;
  }

  /**
   * Get long_name
   *
   * @return string
   */
  public function getLongName()
  {
    return $this->long_name;
  }

  /**
   * Set poc_name
   *
   * @param string $pocName
   * @return Region
   */
  public function setPocName($pocName)
  {
    $this->poc_name = $pocName;

    return $this;
  }

  /**
   * Get poc_name
   *
   * @return string
   */
  public function getPocName()
  {
    return $this->poc_name;
  }

  /**
   * Set poc_email
   *
   * @param string $pocEmail
   * @return Region
   */
  public function setPocEmail($pocEmail)
  {
    $this->poc_email = $pocEmail;

    return $this;
  }

  /**
   * Get poc_email
   *
   * @return string
   */
  public function getPocEmail()
  {
    return $this->poc_email;
  }

  /**
   * Set ref_admin_name
   *
   * @param string $refAdminName
   * @return Region
   */
  public function setRefAdminName($refAdminName)
  {
    $this->ref_admin_name = $refAdminName;

    return $this;
  }

  /**
   * Get ref_admin_name
   *
   * @return string
   */
  public function getRefAdminName()
  {
    return $this->ref_admin_name;
  }

  /**
   * Set ref_admin_email
   *
   * @param string $refAdminEmail
   * @return Region
   */
  public function setRefAdminEmail($refAdminEmail)
  {
    $this->ref_admin_email = $refAdminEmail;

    return $this;
  }

  /**
   * Get ref_admin_email
   *
   * @return string
   */
  public function getRefAdminEmail()
  {
    return $this->ref_admin_email;
  }
}