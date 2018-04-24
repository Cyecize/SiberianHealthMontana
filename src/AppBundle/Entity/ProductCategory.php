<?php

namespace AppBundle\Entity;

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
class ProductCategory
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
     * @ORM\Column(name="parent_id", type="integer", nullable=true )
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
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Product")
     * @var Product[]
     */
    private $products;


    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Product", mappedBy="fatherCategory")
     */
    private $productsMappedByProduct;


    /**
     * @var ProductCategory
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ProductCategory", inversedBy="subCategories")
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     */

    private $parentCategory;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ProductCategory", mappedBy="parentCategory", fetch="EAGER")
     * @JoinColumn(name="id", referencedColumnName="parent_id")
     */

    private $subCategories;


    public function __construct()
    {
        $this->parentId = 0; //main category by default
        $this->products = new ArrayCollection();
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

    public function getCategoryNameForCategoryCreate()
    {
        if (!$this->hasParentCategory())
            return $this->getCategoryName();
        $catNames = explode("|", $this->getCategoryName() . "|" . $this->parentCategory->getCategoryNameForCategoryCreate());
        $catNames = array_reverse($catNames);
        $a = implode("\\", $catNames);

        return $a;
    }

    public function getCategoryNameForLinks()
    {
        if (!$this->hasParentCategoryForNav())
            return $this->getCategoryName();
        $catNames = explode("|", $this->getCategoryName() . "|" . $this->parentCategory->getCategoryNameForLinks());
        $catNames = array_reverse($catNames);
        $a = implode(" \ ", $catNames);

        return $a;
    }

    /**
     * @return ProductCategory[]
     */
    public function getSubCategories()
    {
        return $this->subCategories;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function getParentCategory(): ProductCategory
    {
        return $this->parentCategory;
    }

    /**
     * README
     * main category is with id 0 and parentId NULL that is why i do these checks
     */
    public function hasParentCategory()
    {
        return $this->parentId != -1;
    }

    public function hasParentCategoryForNav(){
        return $this->parentId != 0;
    }

    public function getParentCategories()
    {
        $cats = array();
        $currentCat = $this;
        while (true) {
            if (!$currentCat->hasParentCategory())
                break;
            $currentCat = $currentCat->getParentCategory();
            $cats[] = $currentCat;
        }

        return $cats;
    }

    /**
     * @return Product[]
     */
    public function getAllProductsRecursive() : array {
        $prods = [];
        $thisProds = [];
        foreach ($this->products as $pp){
            if(!$pp->getHidden()){
                $thisProds[] = $pp;
            }
        }
        $prods = array_merge($prods, $thisProds);
        foreach ($this->getSubCategories() as $category){
            $prods = array_merge($prods, $category->getAllProductsRecursive());
        }

        $resRaw =  array_unique($prods);
        $result = [];
        foreach ($resRaw as $item) {
            $result[] = $item;
        }
        return $result;
    }

    public function __toString()
    {
        return $this->categoryName;
    }
}

