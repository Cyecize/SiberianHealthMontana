<?php
/**
 * Created by PhpStorm.
 * User: Cyecize
 * Date: 2/9/2018
 * Time: 11:41 PM
 */

namespace AppBundle\Service;


use AppBundle\Constant\ConstantValues;
use AppBundle\Constant\PathConstants;
use AppBundle\Entity\ProductCategory;
use AppBundle\Entity\SocialLink;
use AppBundle\Entity\Township;
use AppBundle\Util\CharacterTranslator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class BasicInformator
{

    private $entityManagerInterface;
    private $errorMessage;
    private $productManager;

    /**
     * BasicInformator constructor.
     * @param EntityManagerInterface $em
     * @param ProductManager $productManagerr
     */
    public function __construct(EntityManagerInterface $em,  ProductManager $productManagerr)
    {
        $this->entityManagerInterface = $em;
        $this->errorMessage = null;
        $this->productManager = $productManagerr;
    }

    public function getSocialLinks()
    {
        //return $this->repositoty->flush();
        return $this->entityManagerInterface->getRepository(SocialLink::class)->findAll();
        //return $this->getDoctrine()->getManager()->getRepository(SocialLink::class)->findAll();
    }

    /**
     * @return array
     */
    public function getRandomProds() : array {
        return $this->productManager->getRandomProducts(ConstantValues::$MAX_PRODUCTS_PER_SLIDE);
    }

    public function getBestSellers() : array {
       return $this->productManager->getTrendingProducts(ConstantValues::$MAX_PRODUCTS_PER_SLIDE, rand(0, 20));
    }

    /**
     * @return array
     */
    public function getMainCategories(): array
    {
        return $this->getEntityManager()->getRepository(ProductCategory::class)
            ->findBy(array('parentId' => array(0,-1)),array('id' => 'ASC'));
    }

    /**
     * @return Township[]
     */
    public function getTownships() : array {
        return $this->entityManagerInterface->getRepository(Township::class)->findAll();
    }

    public function getEntityManager()
    {
        return $this->entityManagerInterface;
    }

    public function websiteName()
    {
        return ConstantValues::$WEBSITE_NAME;
    }


    public function setError($msg)
    {
        $this->errorMessage = $msg;
    }

    public function getError()
    {
        return $this->errorMessage;
    }

    public function getPageBanner()
    {
        return PathConstants::$PAGE_BANNER_PATH;
    }

    public function maxProductsPerPage()
    {
        return ConstantValues::$MAX_PRODUCTS_PER_PAGE;
    }

    public function WEBSITE_NAME_PART_ONE()
    {
        return "Siberian Health";
    }

    public  function  WEBSITE_NAME_PART_TWO(){
        return " Montana";
    }

    public  function getCharacterTranslator() : CharacterTranslator{
        return new CharacterTranslator();
    }

}