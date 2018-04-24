<?php
/**
 * Created by PhpStorm.
 * User: Cyecize
 * Date: 2/10/2018
 * Time: 3:30 PM
 */

namespace AppBundle\Service;


use AppBundle\Entity\Product;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class ProductManager
{
    private $entityManagerInterface;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManagerInterface = $em;
    }

    public function getTrendingProducts(int $limit, int $offset) {
       return $this->entityManagerInterface->getRepository(Product::class)->findBy(
            array('hidden'=>false), array('soldCount'=>'DESC'), $limit, $offset
        );
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return Product[]
     */
    public function getNewestProducts(int $limit, int $offset) : array {
        return $this->entityManagerInterface->getRepository(Product::class)->findBy(
            array('hidden'=>false), array('id'=>'ASC'), $limit, $offset
        );
    }

    public function getSimilarProducts($limit, $offset, $categoryId){
        return $this->entityManagerInterface->getRepository(Product::class)->findBy(
            array('categoryId'=>$categoryId, 'hidden'=>false), array(), $limit, $offset
        );
    }

    public function getRandomProducts($numberOfProds){
        return $this->entityManagerInterface->getRepository(Product::class)->findBy(
          array('hidden'=>false), array(), $numberOfProds, rand(0,8)
        );
    }

}