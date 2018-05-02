<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PasswordRecovery
 *
 * @ORM\Table(name="password_recoveries")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PasswordRecoveryRepository")
 */
class PasswordRecovery
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
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_used", type="boolean")
     */
    private $isUsed;

    /**
     * @var int
     *
     * @ORM\Column(name="time", type="bigint")
     */
    private $time;

    public function  __construct()
    {
        $this->time = time();
        $this->code = ceil($this->time /rand(1,30)) * rand(1,30). "";
        $this->isUsed = false;
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
     * Set userId
     *
     * @param integer $userId
     *
     * @return PasswordRecovery
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return PasswordRecovery
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set isUsed
     *
     * @param boolean $isUsed
     *
     * @return PasswordRecovery
     */
    public function setIsUsed($isUsed)
    {
        $this->isUsed = $isUsed;

        return $this;
    }

    /**
     * Get isUsed
     *
     * @return bool
     */
    public function getIsUsed()
    {
        return $this->isUsed;
    }

    /**
     * Set time
     *
     * @param integer $time
     *
     * @return PasswordRecovery
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }
}

