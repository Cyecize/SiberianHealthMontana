<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductOrder
 *
 * @ORM\Table(name="product_orders")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductOrderRepository")
 */
class ProductOrder
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
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="shopping_cart", type="string", length=255)
     */
    private $shoppingCart;

    /**
     * @var float
     *
     * @ORM\Column(name="total_price", type="float")
     */
    private $totalPrice;

    /**
     * @var bool
     *
     * @ORM\Column(name="accepted", type="boolean")
     */
    private $accepted;

    /**
     * @var string
     *
     * @ORM\Column(name="full_name", type="string", length=100)
     */
    private $fullName;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=45)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=15)
     */
    private $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="residential", type="string", length=45)
     */
    private $residential;

    /**
     * @var string
     *
     * @ORM\Column(name="post_code", type="string", length=20)
     */
    private $postCode;

    /**
     * @var string
     *
     * @ORM\Column(name="township", type="string", length=45)
     */
    private $township;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    public function __construct()
    {
        $this->accepted = false;
        $this->date =  new \DateTime('now', new \DateTimeZone('Europe/Sofia'));
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
     * @return ProductOrder
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
     * Set shoppingCart
     *
     * @param string $shoppingCart
     *
     * @return ProductOrder
     */
    public function setShoppingCart($shoppingCart)
    {
        $this->shoppingCart = $shoppingCart;

        return $this;
    }

    /**
     * Get shoppingCart
     *
     * @return string
     */
    public function getShoppingCart()
    {
        return $this->shoppingCart;
    }

    /**
     * Set totalPrice
     *
     * @param float $totalPrice
     *
     * @return ProductOrder
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * Get totalPrice
     *
     * @return float
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * Set accepted
     *
     * @param boolean $accepted
     *
     * @return ProductOrder
     */
    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;

        return $this;
    }

    /**
     * Get accepted
     *
     * @return bool
     */
    public function getAccepted()
    {
        return $this->accepted;
    }

    /**
     * Set fullName
     *
     * @param string $fullName
     *
     * @return ProductOrder
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return ProductOrder
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
     * Set phoneNumber
     *
     * @param string $phoneNumber
     *
     * @return ProductOrder
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return ProductOrder
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
     * @return ProductOrder
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
     * @param string $postCode
     *
     * @return ProductOrder
     */
    public function setPostCode($postCode)
    {
        $this->postCode = $postCode;

        return $this;
    }

    /**
     * Get postCode
     *
     * @return string
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * Set township
     *
     * @param string $township
     *
     * @return ProductOrder
     */
    public function setTownship($township)
    {
        $this->township = $township;

        return $this;
    }

    /**
     * Get township
     *
     * @return string
     */
    public function getTownship()
    {
        return $this->township;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
    }



    /*
     *@param UserAddress $address
     */
    public function addAddress(UserAddress $address) : void{
        $this->fullName = $address->getFullName();
        $this->phoneNumber = $address->getPhoneNumber();
        $this->township = $address->getTownShip()->getTownName();
        $this->postCode = $address->getPostCode();
        $this->residential = $address->getResidential();
        $this->address = $address->getAddress();
    }

    /**
     * @return string
     */
    public  function getDateAsString() : string{
        return $this->date->format("d.m,Y (h:i:s)");
    }

}

