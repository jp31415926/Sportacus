<?php

namespace Scheduler\SchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Message
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Scheduler\SchBundle\Entity\MessageRepository")
 */
class Message
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
   * @ORM\Column(name="delivery_date", type="datetime")
   */
  private $delivery_date;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="date", type="datetime")
   */
  private $date;

  /**
   * @var integer
   *
   * One-To-Many, Unidirectional
   * @ORM\ManyToMany(targetEntity="User")
   * @ORM\JoinTable(name="msg_user",
   *      joinColumns={@ORM\JoinColumn(name="msg_id", referencedColumnName="id")},
   *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
   *      )
   */
  private $sent_to;

  /**
   * @var integer
   *
   * @ORM\ManyToOne(targetEntity="User")
   */
  private $sent_from;

  /**
   * @var string
   *
   * @ORM\Column(name="subject", type="string", length=144)
   */
  private $subject;

  /**
   * @var string
   *
   * @ORM\Column(name="message", type="text", nullable=true)
   */
  private $message;

  /**
   * @var integer
   *
   * @ORM\Column(name="media_type", type="smallint")
   */
  private $media_type;

  /**
   * @var integer
   *
   * @ORM\Column(name="type", type="integer")
   */
  private $type;

  /**
   * @var string
   *
   * @ORM\Column(name="data", type="string", length=255, nullable=true)
   */
  private $data;


  public function __construct()
  {
    $this->sent_to = new \Doctrine\Common\Collections\ArrayCollection();
    $this->setMediaType(0);
    $this->setType(0);
    $this->setData('');
    $this->setDate(new \DateTime);
  }

  /**
   * @Assert\IsTrue(message = "Message has errors")
   */
  public function isAssignmentsLegal()
  {
    //$this->setSentFrom(-- set to current user! --);

    return true;
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
   * Set delivery_date
   *
   * @param \DateTime $deliveryDate
   * @return Message
   */
  public function setDeliveryDate($deliveryDate)
  {
    $this->delivery_date = $deliveryDate;

    return $this;
  }

  /**
   * Get delivery_date
   *
   * @return \DateTime
   */
  public function getDeliveryDate()
  {
    return $this->delivery_date;
  }

  /**
   * Set date
   *
   * @param \DateTime $date
   * @return Message
   */
  public function setDate($date)
  {
    $this->date = $date;

    return $this;
  }

  /**
   * Get date
   *
   * @return \DateTime
   */
  public function getDate()
  {
    return $this->date;
  }

  /**
   * Add sent_to
   *
   * @param \Scheduler\SchBundle\Entity\User $sentTo
   * @return Message
   */
  public function addSentTo(\Scheduler\SchBundle\Entity\User $sentTo)
  {
    $this->sent_to[] = $sentTo;

    return $this;
  }

  /**
   * Remove sent_to
   *
   * @param \Scheduler\SchBundle\Entity\User $sentTo
   */
  public function removeSentTo(\Scheduler\SchBundle\Entity\AgeGroup $sentTo)
  {
    $this->sent_to->removeElement($sentTo);
  }

  /**
   * Get sent_to
   *
   * @return Doctrine\Common\Collections\Collection
   */
  public function getSentTo()
  {
    return $this->sent_to;
  }

  /**
   * Set sent_from
   *
   * @param integer $sentFrom
   * @return Message
   */
  public function setSentFrom($sentFrom)
  {
    $this->sent_from = $sentFrom;

    return $this;
  }

  /**
   * Get sent_from
   *
   * @return integer
   */
  public function getSentFrom()
  {
    return $this->sent_from;
  }

  /**
   * Set subject
   *
   * @param string $subject
   * @return Message
   */
  public function setSubject($subject)
  {
    $this->subject = $subject;

    return $this;
  }

  /**
   * Get subject
   *
   * @return string
   */
  public function getSubject()
  {
    return $this->subject;
  }

  /**
   * Set message
   *
   * @param string $message
   * @return Message
   */
  public function setMessage($message)
  {
    $this->message = $message;

    return $this;
  }

  /**
   * Get message
   *
   * @return string
   */
  public function getMessage()
  {
    return $this->message;
  }

  /**
   * Set media_type
   *
   * @param integer $mediaType
   * @return Message
   */
  public function setMediaType($mediaType)
  {
    $this->media_type = $mediaType;

    return $this;
  }

  /**
   * Get media_type
   *
   * @return integer
   */
  public function getMediaType()
  {
    return $this->media_type;
  }

  /**
   * Set type
   *
   * @param integer $type
   * @return Message
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
   * Set data
   *
   * @param string $data
   * @return Message
   */
  public function setData($data)
  {
    $this->data = $data;

    return $this;
  }

  /**
   * Get data
   *
   * @return string
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * Get string representation of class
   *
   * @return string
   */
  public function __toString()
  {
    return $this->getId() + ' ' + $this->getSubject();
  }

}
