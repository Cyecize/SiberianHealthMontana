<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * ShoppingCart
 *
 * @ORM\Table(name="shopping_carts")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ShoppingCartRepository")
 */
class ShoppingCart
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
     * @ORM\Column(name="user_id", type="integer", unique=true)
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="raw_products", type="text")
     */
    private $rawProducts;


    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\User", mappedBy="cart", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $users;


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
     * @return ShoppingCart
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
     * Set rawProducts
     *
     * @param string $rawProducts
     *
     * @return ShoppingCart
     */
    public function setRawProducts($rawProducts)
    {
        $this->rawProducts = $rawProducts;

        return $this;
    }

    /**
     * Get rawProducts
     *
     * @return string
     */
    public function getRawProducts()
    {
        return $this->rawProducts;
    }

    public function getDecodedProducts(){
        $res = json_decode($this->rawProducts, true);
        if($res == null)
            return array();
        return $res;
    }
}

