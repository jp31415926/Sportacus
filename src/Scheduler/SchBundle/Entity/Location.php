<?php

namespace Scheduler\SchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
//use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Cerad\Bundle\ProjectBundle\Entity\ProjectLocationTrait;

/**
 * Scheduler\SchBundle\Entity\Location
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Scheduler\SchBundle\Entity\LocationRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields="name", message="The name of a location must be unique!")
 *
 */
class Location implements \ArrayAccess
{
  use ProjectLocationTrait;

  /**
   * @var integer $id
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var string $name
   *
   * @ORM\Column(name="name", type="string", length=64, unique=true)
   * @Assert\NotBlank()
   */
  private $name;

  /**
   * @var string $long_name
   *
   * @ORM\Column(name="long_name", type="string", length=255, nullable=true)
   */
  private $long_name;

  /**
   * @var string $street1
   *
   * @ORM\Column(name="street1", type="string", length=128, nullable=true)
   */
  private $street1;

  /**
   * @var string $street2
   *
   * @ORM\Column(name="street2", type="string", length=128, nullable=true)
   */
  private $street2;

  /**
   * @var string $city
   *
   * @ORM\Column(name="city", type="string", length=64, nullable=true)
   */
  private $city;

  /**
   * @var string $state
   *
   * @ORM\Column(name="state", type="string", length=32, nullable=true)
   */
  private $state;

  /**
   * @var string $zip
   *
   * @ORM\Column(name="zip", type="string", length=10, nullable=true)
   */
  private $zip;

  /**
   * @var integer $latitude
   *
   * @ORM\Column(name="latitude", type="string", length=32, nullable=true)
   */
  private $latitude;

  /**
   * @var integer $longitude
   *
   * @ORM\Column(name="longitude", type="string", length=32,  nullable=true)
   */
  private $longitude;

  /**
   * @var string $poc_name
   *
   * @ORM\Column(name="poc_name", type="string", length=128, nullable=true)
   */
  private $poc_name;

  /**
   * @var string $poc_phone1
   *
   * @ORM\Column(name="poc_phone1", type="string", length=32, nullable=true)
   */
  private $poc_phone1;

  /**
   * @var string $poc_phone2
   *
   * @ORM\Column(name="poc_phone2", type="string", length=32, nullable=true)
   */
  private $poc_phone2;

  /**
   * @var string $poc_email1
   *
   * @ORM\Column(name="poc_email1", type="string", length=128, nullable=true)
   */
  private $poc_email1;

  /**
   * @var string $poc_email2
   *
   * @ORM\Column(name="poc_email2", type="string", length=128, nullable=true)
   */
  private $poc_email2;

  /**
   * @var string $url
   *
   * @ORM\Column(name="url", type="string", length=255, nullable=true)
   */
  private $url;

  /**
   * @ ORM\ManyToMany(targetEntity="AgeGroup")
   * @ ORM\JoinTable(name="location_agegroups",
   *      joinColumns={@ORM\JoinColumn(name="location_id", referencedColumnName="id")},
   *      inverseJoinColumns={@ORM\JoinColumn(name="agegroup_id", referencedColumnName="id")}
   *      )
   **/
//    private $agegroups;

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
   * Set street1
   *
   * @param string $street1
   * @return Location
   */
  public function setStreet1($street1)
  {
    $this->street1 = $street1;

    return $this;
  }

  /**
   * Get street1
   *
   * @return string
   */
  public function getStreet1()
  {
    return $this->street1;
  }

  /**
   * Set street2
   *
   * @param string $street2
   * @return Location
   */
  public function setStreet2($street2)
  {
    $this->street2 = $street2;

    return $this;
  }

  /**
   * Get street2
   *
   * @return string
   */
  public function getStreet2()
  {
    return $this->street2;
  }

  /**
   * Set city
   *
   * @param string $city
   * @return Location
   */
  public function setCity($city)
  {
    $this->city = $city;

    return $this;
  }

  /**
   * Get city
   *
   * @return string
   */
  public function getCity()
  {
    return $this->city;
  }

  /**
   * Set state
   *
   * @param string $state
   * @return Location
   */
  public function setState($state)
  {
    $this->state = $state;

    return $this;
  }

  /**
   * Get state
   *
   * @return string
   */
  public function getState()
  {
    return $this->state;
  }

  /**
   * Set zip
   *
   * @param string $zip
   * @return Location
   */
  public function setZip($zip)
  {
    $this->zip = $zip;

    return $this;
  }

  /**
   * Get zip
   *
   * @return string
   */
  public function getZip()
  {
    return $this->zip;
  }

  /**
   * Set latitude
   *
   * @param integer $latitude
   * @return Location
   */
  public function setLatitude($latitude)
  {
    $this->latitude = $latitude;

    return $this;
  }

  /**
   * Get latitude
   *
   * @return integer
   */
  public function getLatitude()
  {
    return $this->latitude;
  }

  /**
   * Set longitude
   *
   * @param integer $longitude
   * @return Location
   */
  public function setLongitude($longitude)
  {
    $this->longitude = $longitude;

    return $this;
  }

  /**
   * Get longitude
   *
   * @return integer
   */
  public function getLongitude()
  {
    return $this->longitude;
  }

  /**
   * Set poc_name
   *
   * @param string $pocName
   * @return Location
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
   * Set poc_phone1
   *
   * @param string $pocPhone1
   * @return Location
   */
  public function setPocPhone1($pocPhone1)
  {
    $this->poc_phone1 = $pocPhone1;

    return $this;
  }

  /**
   * Get poc_phone1
   *
   * @return string
   */
  public function getPocPhone1()
  {
    return $this->poc_phone1;
  }

  /**
   * Set poc_phone2
   *
   * @param string $pocPhone2
   * @return Location
   */
  public function setPocPhone2($pocPhone2)
  {
    $this->poc_phone2 = $pocPhone2;

    return $this;
  }

  /**
   * Get poc_phone2
   *
   * @return string
   */
  public function getPocPhone2()
  {
    return $this->poc_phone2;
  }

  /**
   * Set poc_email1
   *
   * @param string $pocEmail1
   * @return Location
   */
  public function setPocEmail1($pocEmail1)
  {
    $this->poc_email1 = $pocEmail1;

    return $this;
  }

  /**
   * Get poc_email1
   *
   * @return string
   */
  public function getPocEmail1()
  {
    return $this->poc_email1;
  }

  /**
   * Set poc_email2
   *
   * @param string $pocEmail2
   * @return Location
   */
  public function setPocEmail2($pocEmail2)
  {
    $this->poc_email2 = $pocEmail2;

    return $this;
  }

  /**
   * Get poc_email2
   *
   * @return string
   */
  public function getPocEmail2()
  {
    return $this->poc_email2;
  }

  /**
   * Set url
   *
   * @param string $url
   * @return Location
   */
  public function setUrl($url)
  {
    $this->url = $url;

    return $this;
  }

  /**
   * Get url
   *
   * @return string
   */
  public function getUrl()
  {
    return $this->url;
  }


  public function __construct()
  {
    //$this->agegroups = new \Doctrine\Common\Collections\ArrayCollection();
    $this->setCreated(new \DateTime());
    $this->setUpdated(new \DateTime());
  }

  /**
   * @ORM\PreUpdate
   */
  public function setUpdatedValue()
  {
    $this->setUpdated(new \DateTime());
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
   * Reset entity for clone
   *
   * @return Location
   */
  public function resetForClone()
  {
    // reset any fields for successful clone
    $this->id = null;
    return $this;
  }

  /**
   * Set name
   *
   * @param string $name
   * @return Location
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
   * Set created
   *
   * @param \DateTime $created
   * @return Location
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
   * @return Location
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
   * Add agegroups
   *
   * @param Scheduler\SchBundle\Entity\AgeGroup $agegroups
   * @return Location
   */
  public function addAgegroup(\Scheduler\SchBundle\Entity\AgeGroup $agegroups)
  {
    $this->agegroups[] = $agegroups;

    return $this;
  }

  /**
   * Remove agegroups
   *
   * @param Scheduler\SchBundle\Entity\AgeGroup $agegroups
   */
  public function removeAgegroup(\Scheduler\SchBundle\Entity\AgeGroup $agegroups)
  {
    $this->agegroups->removeElement($agegroups);
  }

  /**
   * Get agegroups
   *
   * @return Doctrine\Common\Collections\Collection
   */
  public function getAgegroups()
  {
    return $this->agegroups;
  }

  public function __toString()
  {
    return $this->getName();
  }


  /**
   * Set long_name
   *
   * @param string $longName
   * @return Location
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
   * Get long name & address
   *
   * @return string
   */
  public function getNameAddr()
  {
    $addr = $this->long_name;

    if ($this->street1) {
      if ($addr) $addr .= ', ';
      $addr .= $this->street1;
    }
    
    if ($this->street2) {
      if ($addr) $addr .= ', ';
      $addr .= $this->street2;
    }
    
    if ($this->city) {
      if ($addr) $addr .= ', ';
      $addr .= $this->city;
    }
    
    if ($this->state) {
      if ($addr) $addr .= ', ';
      $addr .= $this->state;
    }
    
    if ($this->zip) {
      if ($addr) $addr .= ' ';
      $addr .= $this->zip;
    }
    
    return $addr;
  }
}
