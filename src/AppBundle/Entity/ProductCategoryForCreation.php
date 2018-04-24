<?php

namespace AppBundle\Entity;

use AppBundle\Entity\ProductCategory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * ProductCategory
 *
 * @ORM\Table(name="product_categories")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductCategoryRepository")
 */
class ProductCategoryForCreation
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
     * @ORM\Column(name="parent_id", type="integer" )
     */
    public $parentId;

    /**
     * @var string
     *
     * @ORM\Column(name="category_name", type="string", length=50)
     */
    private $categoryName;

    /**
     * @var string
     *
     * @ORM\Column(name="latin_name", type="string", length=70)
     */
    private $latinName;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Product", mappedBy="fatherCategory")
     */
    private $products;


    public function __construct()
    {
        $this->parentId = 0; //main category by default
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
     * Set parentId
     *
     * @param integer $parentId
     *
     * @return ProductCategory
     */
    public function setParentId($parentId)
    {
        if ($parentId != null)
            $this->parentId = $parentId;
        return $this;
    }

    /**
     * Get parentId
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set categoryName
     *
     * @param string $categoryName
     *
     * @return ProductCategory
     */
    public function setCategoryName($categoryName)
    {
        $this->categoryName = $categoryName;

        return $this;
    }

    /**
     * Get categoryName
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * Set latinName
     *
     * @param string $latinName
     *
     * @return ProductCategory
     */
    public function setLatinName($latinName)
    {
        $this->latinName = $latinName;

        return $this;
    }

    /**
     * Get latinName
     *
     * @return string
     */
    public function getLatinName()
    {
        return $this->latinName;
    }

}

