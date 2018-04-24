<?php

namespace AppBundle\Entity;

use AppBundle\Constant\Config;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User implements UserInterface
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
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=50, unique=true)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=50, unique=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    private $confPassword;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_registered", type="datetime")
     */
    private $dateRegistered;

    /**
     * @var int
     *
     * @ORM\Column(name="authority_level", type="integer")
     */
    private $authorityLevel;


    /**
     * @var ShoppingCart
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ShoppingCart", inversedBy="users" , fetch="EAGER")
     *  @ORM\JoinColumn(name="id", referencedColumnName="user_id")
     */
    private $cart;


    public function __construct()
    {
        $this->authorityLevel = Config::$CASUAL_USER_LEVEL;
        $this->dateRegistered = new \DateTime('now', new \DateTimeZone('Europe/Sofia'));
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
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
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
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return mixed
     */
    public function getConfPassword()
    {
        return $this->confPassword;
    }

    /**
     * @param mixed $confPassword
     */
    public function setConfPassword($confPassword)
    {
        $this->confPassword = $confPassword;
    }

    /**
     * Set dateRegistered
     *
     * @param \DateTime $dateRegistered
     *
     * @return User
     */
    public function setDateRegistered($dateRegistered)
    {
        $this->dateRegistered = $dateRegistered;

        return $this;
    }

    /**
     * Get dateRegistered
     *
     * @return \DateTime
     */
    public function getDateRegistered()
    {
        return $this->dateRegistered;
    }

    /**
     * Set authorityLevel
     *
     * @param integer $authorityLevel
     *
     * @return User
     */
    public function setAuthorityLevel($authorityLevel)
    {
        $this->authorityLevel = $authorityLevel;

        return $this;
    }

    /**
     * Get authorityLevel
     *
     * @return int
     */
    public function getAuthorityLevel()
    {
        return $this->authorityLevel;
    }


    /**
     * @return mixed
     */
    public function getCart(): ShoppingCart
    {
        return $this->cart;
    }


    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return array('ROLE_USER');
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        //i might implement salt
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        //wtf is that
    }

    public function isValidUsername(): bool
    {
        if ($this->username == null)
            return false;
        $username = trim($this->username);
        $username = explode(" ", $username);
        return count($username) == 1;
    }


}

