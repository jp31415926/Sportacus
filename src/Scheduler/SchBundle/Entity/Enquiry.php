<?php
// src/Scheduler/SchBundle/Entity/Enquiry.php

namespace Scheduler\SchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

//use Symfony\Component\Validator\Mapping\ClassMetadata;
//use Symfony\Component\Validator\Constraints\NotBlank;
//use Symfony\Component\Validator\Constraints\Email;
//use Symfony\Component\Validator\Constraints\MinLength;
//use Symfony\Component\Validator\Constraints\MaxLength;

/**
 * Scheduler\SchBundle\Entity\Enquiry
 *
 * @ORM\Table()
 */
class Enquiry
{
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
   * @ORM\Column(name="name", type="string", length=64)
   * @Assert\NotBlank()
   */
  private $name;

  /**
   * @var string $email
   *
   * @ORM\Column(name="email", type="string", length=255)
   * @Assert\Email(
   *     message = "The email '{{ value }}' is not a valid email address.",
   *     checkMX = true
   * )
   */
  private $email;

  /**
   * @var string $subject
   *
   * @ORM\Column(name="subject", type="string", length=50)
   * @Assert\NotBlank()
   */
  private $subject;

  /**
   * @var string $body
   *
   * @ORM\Column(name="body", type="text")
   */
  private $body;

  /**
   * @var datetime $created
   *
   * @ORM\Column(name="created", type="datetime")
   */
  private $created;

  /**
   * @var datetime $updated
   *
   * @ORM\Column(name="updated", type="datetime")
   */
  private $updated;


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
   * @return Enquiry
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
   * Set email
   *
   * @param string $email
   * @return Enquiry
   */
  public function setEmail($email)
  {
    $this->email = $email;

    return $this;
  }

  /**
   * Get email
   *
   * @return string
   */
  public function getEmail()
  {
    return $this->email;
  }

  /**
   * Set subject
   *
   * @param string $subject
   * @return Enquiry
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
   * Set body
   *
   * @param string $body
   * @return Enquiry
   */
  public function setBody($body)
  {
    $this->body = $body;

    return $this;
  }

  /**
   * Get body
   *
   * @return string
   */
  public function getBody()
  {
    return $this->body;
  }


//    public static function loadValidatorMetadata(ClassMetadata $metadata)
//    {
  //$metadata->addPropertyConstraint('name', new NotBlank());

  //$metadata->addPropertyConstraint('email', new Email(array('message' => 'That email address is invalid!')));

  //$metadata->addPropertyConstraint('subject', new NotBlank());
  //$metadata->addPropertyConstraint('subject', new MaxLength(50));

  //$metadata->addPropertyConstraint('body', new MinLength(50));
//    }

  /**
   * Set created
   *
   * @param \DateTime $created
   * @return Enquiry
   */
  public function setCreated($created)
  {
    $this->created_time = $created;

    return $this;
  }

  /**
   * Get created_time
   *
   * @return \DateTime
   */
  public function getCreated()
  {
    return $this->created;
  }

  /**
   * Set updated_time
   *
   * @param \DateTime $updated
   * @return Enquiry
   */
  public function setUpdated($updated)
  {
    $this->updated_time = $updated;

    return $this;
  }

  /**
   * Get updated_time
   *
   * @return \DateTime
   */
  public function getUpdated()
  {
    return $this->updated;
  }
}
