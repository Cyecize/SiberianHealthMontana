<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Notification
 *
 * @ORM\Table(name="notifications")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NotificationRepository")
 */
class Notification
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="target_id", type="integer")
     */
    private $targetId;

    /**
     * @var string
     *
     * @ORM\Column(name="notification_type", type="string", length=45)
     */
    private $notificationType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;


    public function __construct()
    {
        $this->date = new \DateTime('now', new \DateTimeZone('Europe/Sofia'));
    }

    /**
     * @return void
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set targetId
     *
     * @param integer $targetId
     *
     * @return Notification
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;

        return $this;
    }

    /**
     * Get targetId
     *
     * @return int
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * Set notificationType
     *
     * @param string $notificationType
     *
     * @return Notification
     */
    public function setNotificationType($notificationType)
    {
        $this->notificationType = $notificationType;

        return $this;
    }

    /**
     * Get notificationType
     *
     * @return string
     */
    public function getNotificationType()
    {
        return $this->notificationType;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Notification
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
     * Set content
     *
     * @param string $content
     *
     * @return Notification
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param Notification $notification
     */
    public function copy(Notification $notification){
        $this->notificationType = $notification->getNotificationType();
        $this->content = $notification->getContent();
    }

    /**
     * @return string
     */
    public  function getDateAsString() : string{
        return $this->date->format("d.m,Y (h:i:s)");
    }


    public function getSummary(){
        if (strlen($this->getContent()) < 50){
            return $this->getContent();
        }else
            return substr($this->getContent(), 0,49);
    }

}

