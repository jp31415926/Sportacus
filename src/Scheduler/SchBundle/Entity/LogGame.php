<?php

namespace Scheduler\SchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Scheduler\SchBundle\Entity\Game;

/**
 * LogGame
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Scheduler\SchBundle\Entity\LogGameRepository")
 */
class LogGame
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
   * @var User
   *
   * @ORM\Column(name="user_id", type="integer")
   */
  private $user;

  /**
   * @var Game
   *
   * @ORM\Column(name="game_id", type="integer")
   */
  private $game;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="dt", type="datetime")
   */
  private $dt;

  /**
   * @var integer
   *
   * @ORM\Column(name="type", type="integer")
   */
  private $type;

  /**
   * @var string
   *
   * @ORM\Column(name="description", type="text", length=2048)
   */
  private $description;


  public function __construct($game, $description = '', $user = NULL)
  {
    $this->setDt(new \DateTime());
    $this->setGame($game);
    $this->setDescription($description);
    $this->setUser($user);
    $this->setType(0);
  }

  public function __toString()
  {
    return $this->dt . ' ' . $this->game;
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
   * Set game
   *
   * @param integer $game
   * @return LogGame
   */
  public function setGame($game)
  {
    $this->game = $game;

    return $this;
  }

  /**
   * Get game
   *
   * @return integer
   */
  public function getGame()
  {
    return $this->game;
  }

  /**
   * Set dt
   *
   * @param \DateTime $dt
   * @return LogGame
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
   * Set type
   *
   * @param integer $type
   * @return LogGame
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

  /**
   * Set description
   *
   * @param string $description
   * @return LogGame
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
   * Set user
   *
   * @param $user
   * @return LogGame
   */
  public function setUser($user = 0)
  {
    $this->user = $user;

    return $this;
  }

  /**
   * Get user
   *
   * @return \Scheduler\SchBundle\Entity\User
   */
  public function getUser()
  {
    return $this->user;
  }
}
