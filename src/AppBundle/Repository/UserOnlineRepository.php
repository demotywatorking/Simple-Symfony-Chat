<?php

namespace AppBundle\Repository;

/**
 * UserOnlineRepository
 *
 */
class UserOnlineRepository extends \Doctrine\ORM\EntityRepository
{
    public function deleteInactiveUsers(\DateTime $date, int $id, int $channel)
    {
        $this->createQueryBuilder('u')
                ->delete()
                ->where('u.onlineTime <= :date')
                ->andWhere('u.userId != :id')
                ->andWhere('u.channel = :channel')
                ->setParameter('id', $id)
                ->setParameter('date', $date)
                ->setParameter('channel', $channel)
                ->getQuery()->getResult();
    }

}
