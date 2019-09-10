<?php

namespace Scheduler\SchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Log
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Scheduler\SchBundle\Entity\LogRepository")
 */
class Log
{
  /**
   * @var integer
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="dt", type="datetime")
   */
  private $dt;

  /**
   * @var integer
   *
   * @ORM\Column(name="user_id", type="integer")
   */
  private $user;

  /**
   * @var integer
   *
   * @ORM\Column(name="type", type="integer")
   */
  private $type;

  /**
   * @var string
   *
   * @ORM\Column(name="info", type="string", length=255, nullable=true)
   */
  private $info;

  /**
   * @var string
   *
   * @ORM\Column(name="description", type="text", length=2048)
   */
  private $description;

  public function __construct()
  {
    $this->setDt(new \DateTime());
    $this->setUser(NULL);
    $this->setInfo(NULL);
    $this->setType(0);
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
   * Set dt
   *
   * @param \DateTime $dt
   * @return Log
   */
  public function setDt($dt)
  {
    $this->dt = $dt;

    return $this;
  }

  /**
   * Get dt
   *
   * @return \DateTime
   */
  public function getDt()
  {
    return $this->dt;
  }

  /**
   * Set user
   *
   * @param integer $user
   * @return Log
   */
  public function setUser($user)
  {
    $this->user = $user;

    return $this;
  }

  /**
   * Get user
   *
   * @return integer
   */
  public function getUser()
  {
    return $this->user;
  }

  /**
   * Set info
   *
   * @param string $info
   * @return Log
   */
  public function setInfo($info)
  {
    $this->info = $info;

    return $this;
  }

  /**
   * Get info
   *
   * @return string
   */
  public function getInfo()
  {
    return $this->info;
  }

  /**
   * Set description
   *
   * @param string $description
   * @return Log
   */
  public function setDescription($description)
  {
    $this->description = $description;

    return $this;
  }

  /**
   * Get description
   *
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * Set type
   *
   * @param integer $type
   * @return Log
   */
  public function setType($type)
  {
    $this->type = $type;

    return $this;
  }

  /**
   * Get type
   *
   * @return integer
   */
  public function getType()
  {
    return $this->type;
  }
}
