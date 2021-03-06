<?php

namespace AppBundle\Repository;
use AppBundle\Constant\ConstantValues;
use AppBundle\Entity\Product;

/**
 * ProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByNameRegx(string $prodName){
        $qb = $this->createQueryBuilder('product');
        $products = $qb
            ->select('product')
            ->where($qb->expr()->like('product.title', ':title'))
            ->orWhere($qb->expr()->like('product.description', ':title'))
            ->andWhere('product.hidden = false')
            ->setMaxResults(ConstantValues::$MAX_SEARCH_RESULTS)
            ->setParameter("title", "%$prodName%")
            ->getQuery()
            ->getResult();
        return $products;
    }
}
