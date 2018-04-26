<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserAddress
 *
 * @ORM\Table(name="user_addresses")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserAddressRepository")
 */
class UserAddress
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
     * @ORM\Column(name="full_name", type="string", length=100, nullable=false)
     */
    private $fullName;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=15, nullable=false)
     */
    private $phoneNumber;


    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=100)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="residential", type="string", length=45, nullable=false)
     */
    private $residential;

    /**
     * @var int
     *
     * @ORM\Column(name="post_code", type="integer")
     */
    private $postCode;

    /**
     * @var int
     *
     * @ORM\Column(name="township_id", type="integer")
     */
    private $townshipId;

    /**
     * @var Township
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Township", inversedBy="addresses" , fetch="EAGER")
     * @ORM\JoinColumn(name="township_id", referencedColumnName="id")
     */
    private $townShip;

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
     * @return UserAddress
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
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param string $fullName
     */
    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber(string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }


    /**
     * Set address
     *
     * @param string $address
     *
     * @return UserAddress
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set residential
     *
     * @param string $residential
     *
     * @return UserAddress
     */
    public function setResidential($residential)
    {
        $this->residential = $residential;

        return $this;
    }

    /**
     * Get residential
     *
     * @return string
     */
    public function getResidential()
    {
        return $this->residential;
    }

    /**
     * Set postCode
     *
     * @param integer $postCode
     *
     * @return UserAddress
     */
    public function setPostCode($postCode)
    {
        $this->postCode = $postCode;

        return $this;
    }

    /**
     * Get postCode
     *
     * @return int
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * Set townshipId
     *
     * @param integer $townshipId
     *
     * @return UserAddress
     */
    public function setTownshipId($townshipId)
    {
        $this->townshipId = $townshipId;

        return $this;
    }

    /**
     * Get townshipId
     *
     * @return int
     */
    public function getTownshipId()
    {
        return $this->townshipId;
    }

    /**
     * @return Township
     */
    public function getTownShip(): Township
    {
        return $this->townShip;
    }

    /**
     * @param Township $township
     * @return  void
     */
    public function setTownship(Township $township) : void{
        $this->townShip = $township;
    }


    /**
     * @return bool
     */
    public function isAddressValid(): bool
    {
        if ($this->fullName == null || $this->phoneNumber == null || $this->address == null || $this->postCode == null || $this->fullName == "" || $this->phoneNumber == ""
            || $this->address == "")
            return false;
        return true;
    }
}

