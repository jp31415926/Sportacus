<?php

namespace Scheduler\SchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OffTeam
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class OffTeam
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
   * @ORM\Column(name="name", type="string", length=64)
   */
  private $name;

  /**
   * @ORM\ManyToMany(targetEntity="OffPos", cascade={"persist"})
   * @ORM\JoinTable(name="offteam_offpos",
   *      joinColumns={@ORM\JoinColumn(name="offteam_id", referencedColumnName="id")},
   *      inverseJoinColumns={@ORM\JoinColumn(name="offpos_id", referencedColumnName="id")}
   *      )
   **/
  private $positions;


  public function __construct()
  {
    $this->positions = new \Doctrine\Common\Collections\ArrayCollection();
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
   * @return OffTeam
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
   * Add positions
   *
   * @param Scheduler\SchBundle\Entity\OffPos $positions
   * @return Location
   */
  public function addPosition(\Scheduler\SchBundle\Entity\OffPos $positions)
  {
    $this->positions[] = $positions;

    return $this;
  }

  /**
   * Remove positions
   *
   * @param Scheduler\SchBundle\Entity\OffPos $positions
   */
  public function removePosition(\Scheduler\SchBundle\Entity\OffPos $positions)
  {
    $this->positions->removeElement($positions);
  }

  /**
   * Get positions
   *
   * @return Doctrine\Common\Collections\Collection
   */
  public function getPositions()
  {
    return $this->positions;
  }

  public function __toString()
  {
    return $this->getName();
  }

}
