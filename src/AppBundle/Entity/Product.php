<?php

namespace AppBundle\Entity;

use AppBundle\Constant\PathConstants;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Product
 *
 * @ORM\Table(name="products")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductRepository") @ORM\HasLifecycleCallbacks()
 */
class Product
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
     * @ORM\Column(name="category_id", type="integer")
     */
    private $categoryId;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=100)
     */
    private $title;


    /**
     * @var string
     *
     * @ORM\Column(name="sibir_code", type="string", length=50)
     */
    private $sibirCode;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;


    /**
     * @var string
     *
     * @ORM\Column(name="img_path", type="string", length=255)
     */
    private $imgPath;

    /**
     * @var bool
     *
     * @ORM\Column(name="hidden", type="boolean")
     */
    private $hidden;


    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @var int
     *
     * @ORM\Column(name="sold_count", type="integer")
     */
    private $soldCount;

    /**
     * @var string
     * @ORM\Column(name="manufacturer", type="string", nullable=true, length=45)
     */
    private $manufacturer;

    /**
     * @var ArrayCollection()
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ProductCategory", mappedBy="products", fetch="EAGER")
     */
    private $categories;

    /**
     * @var ProductCategory
     * because it is many to many we might need this for displaying proper link paths
     */
    private $currentCategory;

    /**
     * @var ProductCategory
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ProductCategory", inversedBy="products" , fetch="EAGER")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $fatherCategory;



    public  function  __construct()
    {
        $this->categories = new ArrayCollection();
    }

    //getters and setters

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
     * Set title
     *
     * @param string $title
     *
     * @return Product
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set color
     *
     * @param string $color
     *
     * @return Product
     */
    public function setSibirCode($color)
    {
        $this->sibirCode = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getSibirCode()
    {
        return $this->sibirCode;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Product
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set categoryId
     *
     * @param integer $categoryId
     *
     * @return Product
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Get categoryId
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Set imgPath
     *
     * @param string $imgPath
     *
     * @return Product
     */
    public function setImgPath($imgPath)
    {
        $this->imgPath = $imgPath;

        return $this;
    }

    /**
     * Get imgPath
     *
     * @return string
     */
    public function getImgPath()
    {
        return $this->imgPath;
    }

    /**
     * Set hidden
     *
     * @param boolean $hidden
     *
     * @return Product
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Get hidden
     *
     * @return bool
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return Product
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set soldCount
     *
     * @param integer $soldCount
     *
     * @return Product
     */
    public function setSoldCount($soldCount)
    {
        $this->soldCount = $soldCount;

        return $this;
    }

    /**
     * Get soldCount
     *
     * @return int
     */
    public function getSoldCount()
    {
        return $this->soldCount;
    }

    /**
     * @return ProductCategory
     */
    public function getCurrentCategory(): ProductCategory
    {
        return $this->currentCategory;
    }

    /**
     * @param ProductCategory $currentCategory
     */
    public function setCurrentCategory(ProductCategory $currentCategory): void
    {
        $this->currentCategory = $currentCategory;
    }

    /**
     * @return ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return string
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * @param string $manufacturer
     */
    public function setManufacturer(string $manufacturer): void
    {
        $this->manufacturer = $manufacturer;
    }

    /**
     * @param ProductCategory $fatherCategory
     */
    public function setFatherCategory(ProductCategory $fatherCategory): void
    {
        $this->fatherCategory = $fatherCategory;
    }




    //end getters and setters

    public function getPriceForDisplay()
    {
        return sprintf("%0.2f",$this->getPrice()) . " лв.";
    }

    /**
     * @return ProductCategory
     */
    public function getFatherCategory() : ProductCategory
    {
        return $this->fatherCategory;
    }

    public function getImgPathForDisplay()
    {
        return PathConstants::$CATEGORIES_PATH . $this->getFatherCategory()->getLatinName() . DIRECTORY_SEPARATOR . $this->getId() . DIRECTORY_SEPARATOR . $this->getImgPath();
    }

    function getSummary(){
        if (strlen($this->getDescription()) < 500){
            return $this->getDescription();
        }else
            return substr($this->getDescription(), 0,499);
    }

    public function getProperCategory() : ProductCategory{
        if($this->currentCategory != null)
            return $this->currentCategory;
        return $this->fatherCategory;
    }

    /**
     * @return string
     */
    public function getCategoriesNames() : string {
        $res = [];
        foreach ($this->categories as $category){
            $res[] = $category . "";
        }
        return implode(", ", $res);
    }

    //overrides
    public  function __toString()
    {
        return $this->id  . "";
    }

}

