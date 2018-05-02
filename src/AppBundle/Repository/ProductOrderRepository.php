<?php

namespace AppBundle\Repository;
use AppBundle\Entity\User;

/**
 * ProductOrderRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductOrderRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param bool $isOrderFinished
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOrdersCountByParam(bool $isOrderFinished) : int{
        $qb = $this->createQueryBuilder('productOrder');
        $count = $qb->select('count(productOrder.id)')
            ->where('productOrder.accepted = :accepted')
            ->setParameter('accepted', $isOrderFinished)
            ->getQuery()->getSingleScalarResult();

        return $count;
    }

    /**
     * @param User $user
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOrdersCountForUser(User $user){
        $qb = $this->createQueryBuilder('productOrder');
        $count = $qb->select('count(productOrder.id)')
            ->where('productOrder.userId = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()->getSingleScalarResult();
        return $count;
    }


    /**
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOrdersCount() : int{
        $qb = $this->createQueryBuilder('productOrder');
        $count = $qb->select('count(productOrder.id)')
            ->getQuery()->getSingleScalarResult();
        return $count;
    }
}
