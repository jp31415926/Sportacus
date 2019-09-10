<?php

namespace Scheduler\SchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OffPos
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class OffPos
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
   * @var string
   *
   * @ORM\Column(type="string", length=64)
   */
  private $name;

  /**
   * @var string
   *
   * @ORM\Column(type="string", length=16)
   */
  private $shortname;

  /**
   * Difficulty where this position should be available
   *
   * @var integer
   *
   * @ORM\Column(type="integer")
   */
  private $diffavail;

  /**
   * Difficulty where this position should be an option
   *
   * @var integer
   *
   * @ORM\Column(type="integer")
   */
  private $diffvisable;

  /**
   * Difficulty where this position should be required
   *
   * @var integer
   *
   * @ORM\Column(type="integer")
   */
  private $diffreq;


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
   * @return OffPos
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
   * Get string representation of class
   *
   * @return string
   */
  public function __toString()
  {
    return $this->getName();
  }


  /**
   * Set shortname
   *
   * @param string $shortname
   * @return OffPos
   */
  public function setShortname($shortname)
  {
    $this->shortname = $shortname;

    return $this;
  }

  /**
   * Get shortname
   *
   * @return string
   */
  public function getShortname()
  {
    return $this->shortname;
  }

  /**
   * Set diffavail
   *
   * @param integer $diffavail
   * @return OffPos
   */
  public function setDiffavail($diffavail)
  {
    $this->diffavail = $diffavail;

    return $this;
  }

  /**
   * Get diffavail
   *
   * @return integer
   */
  public function getDiffavail()
  {
    return $this->diffavail;
  }

  /**
   * Set diffreq
   *
   * @param integer $diffreq
   * @return OffPos
   */
  public function setDiffreq($diffreq)
  {
    $this->diffreq = $diffreq;

    return $this;
  }

  /**
   * Get diffreq
   *
   * @return integer
   */
  public function getDiffreq()
  {
    return $this->diffreq;
  }

  /**
   * Set diffvisable
   *
   * @param integer $diffvisable
   * @return OffPos
   */
  public function setDiffvisable($diffvisable)
  {
    $this->diffvisable = $diffvisable;

    return $this;
  }

  /**
   * Get diffvisable
   *
   * @return integer
   */
  public function getDiffvisable()
  {
    return $this->diffvisable;
  }
}