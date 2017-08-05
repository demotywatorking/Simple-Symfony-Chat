<?php

namespace AppBundle\Repository;

/**
 * UserOnlineRepository
 *
 */
class UserOnlineRepository extends \Doctrine\ORM\EntityRepository
{
    public function deleteInactiveUsers(\DateTime $date, int $id)
    {
        $this->createQueryBuilder('u')
                ->delete()
                ->where('u.onlineTime <= :date')
                ->andWhere('u.userId != :id')
                ->setParameter('id', $id)
                ->setParameter('date', $date)
                ->getQuery()->getResult();
    }

}
