<?php

namespace Scheduler\SchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OffAssign
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Scheduler\SchBundle\Entity\OffAssignRepository")
 */
class OffAssign
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
   * @var integer
   *
   * @ORM\ManyToOne(targetEntity="Game")
   */
  private $game;

  /**
   * @var integer
   *
   * @ORM\ManyToOne(targetEntity="User")
   */
  private $ref;

  /**
   * @var integer
   *
   * @ORM\ManyToOne(targetEntity="User")
   */
  private $ar1;

  /**
   * @var integer
   *
   * @ORM\ManyToOne(targetEntity="User")
   */
  private $ar2;


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
   * Set game
   *
   * @param \Scheduler\SchBundle\Entity\Game $game
   * @return OffAssign
   */
  public function setGame(\Scheduler\SchBundle\Entity\Game $game = null)
  {
    $this->game = $game;

    return $this;
  }

  /**
   * Get game
   *
   * @return \Scheduler\SchBundle\Entity\Game
   */
  public function getGame()
  {
    return $this->game;
  }

  /**
   * Set ref
   *
   * @param \Scheduler\SchBundle\Entity\User $ref
   * @return OffAssign
   */
  public function setRef(\Scheduler\SchBundle\Entity\User $ref = null)
  {
    $this->ref = $ref;

    return $this;
  }

  /**
   * Get ref
   *
   * @return \Scheduler\SchBundle\Entity\User
   */
  public function getRef()
  {
    return $this->ref;
  }

  /**
   * Set ar1
   *
   * @param \Scheduler\SchBundle\Entity\User $ar1
   * @return OffAssign
   */
  public function setAr1(\Scheduler\SchBundle\Entity\User $ar1 = null)
  {
    $this->ar1 = $ar1;

    return $this;
  }

  /**
   * Get ar1
   *
   * @return \Scheduler\SchBundle\Entity\User
   */
  public function getAr1()
  {
    return $this->ar1;
  }

  /**
   * Set ar2
   *
   * @param \Scheduler\SchBundle\Entity\User $ar2
   * @return OffAssign
   */
  public function setAr2(\Scheduler\SchBundle\Entity\User $ar2 = null)
  {
    $this->ar2 = $ar2;

    return $this;
  }

  /**
   * Get ar2
   *
   * @return \Scheduler\SchBundle\Entity\User
   */
  public function getAr2()
  {
    return $this->ar2;
  }
}