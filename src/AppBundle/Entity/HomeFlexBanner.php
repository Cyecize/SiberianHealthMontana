<?php

namespace AppBundle\Entity;

use AppBundle\Constant\PathConstants;
use Doctrine\ORM\Mapping as ORM;

/**
 * HomeFlexBanner
 *
 * @ORM\Table(name="home_flex_banners")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\HomeFlexBannerRepository")
 */
class HomeFlexBanner
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
     * @ORM\Column(name="img_name", type="string", length=255)
     */
    private $imgName;

    /**
     * @var string
     *
     * @ORM\Column(name="header_one", type="string", length=255, nullable=true)
     */
    private $headerOne;

    /**
     * @var string
     *
     * @ORM\Column(name="header_two", type="string", length=255, nullable=true)
     */
    private $headerTwo;

    /**
     * @var string
     *
     * @ORM\Column(name="header_three", type="string", length=255, nullable=true)
     */
    private $headerThree;

    /**
     * @var bool
     *
     * @ORM\Column(name="hidden", type="boolean")
     */
    private $hidden;


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
     * Set imgName
     *
     * @param string $imgName
     *
     * @return HomeFlexBanner
     */
    public function setImgName($imgName)
    {
        $this->imgName = $imgName;

        return $this;
    }

    /**
     * Get imgName
     *
     * @return string
     */
    public function getImgName()
    {
        return $this->imgName;
    }

    /**
     * Set headerOne
     *
     * @param string $headerOne
     *
     * @return HomeFlexBanner
     */
    public function setHeaderOne($headerOne)
    {
        $this->headerOne = $headerOne;

        return $this;
    }

    /**
     * Get headerOne
     *
     * @return string
     */
    public function getHeaderOne()
    {
        return $this->headerOne;
    }

    /**
     * Set headerTwo
     *
     * @param string $headerTwo
     *
     * @return HomeFlexBanner
     */
    public function setHeaderTwo($headerTwo)
    {
        $this->headerTwo = $headerTwo;

        return $this;
    }

    /**
     * Get headerTwo
     *
     * @return string
     */
    public function getHeaderTwo()
    {
        return $this->headerTwo;
    }

    /**
     * Set headerThree
     *
     * @param string $headerThree
     *
     * @return HomeFlexBanner
     */
    public function setHeaderThree($headerThree)
    {
        $this->headerThree = $headerThree;

        return $this;
    }

    /**
     * Get headerThree
     *
     * @return string
     */
    public function getHeaderThree()
    {
        return $this->headerThree;
    }

    /**
     * Set hidden
     *
     * @param boolean $hidden
     *
     * @return HomeFlexBanner
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

    public function getFixedImgName(){
        return PathConstants::$FLEX_BANNER_PATH . $this->getImgName();
    }
}

