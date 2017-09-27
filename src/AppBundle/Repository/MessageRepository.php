<?php

namespace AppBundle\Repository;

/**
 * MessageRepository
 *
 */
class MessageRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Gets Messages from database from last 24h ordered by date descending
     *
     * @param int $limit limit of messages
     * @param int $channel Channel's id
     *
     * @return array|null Array of Messages Entity of null if no messages
     */
    public function getMessagesFromLastDay(int $limit, int $channel)
    {
        $date = new \DateTime('now');
        $date->modify( '-1 day' );

        return $this->createQueryBuilder('m')
                ->where('m.date >= :date')
                ->andWhere('m.channel = :channel')
                ->andWhere('m.text NOT LIKE :text')
                ->orderBy('m.date', 'DESC')
                ->setParameter('date', $date)
                ->setParameter('channel', $channel)
                ->setParameter('text', '/delete%')
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
    }

    /**
     * Gets Messages from database from last id ordered by id asscending
     *
     * @param int $lastId last message's id
     * @param int $limit limit of messages
     * @param int $channel Channel's id
     *
     * @return array|null Array of Messages or null if no messages
     */
    public function getMessagesFromLastId(int $lastId, int $limit, int $channel)
    {
        return $this->createQueryBuilder('m')
                ->where('m.id > :id')
                ->andWhere('m.channel = :channel')
                ->orderBy('m.id', 'ASC')
                ->setParameter('id', $lastId)
                ->setParameter('channel', $channel)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();
    }

    /**
     * Gets Messages from database after changing channel on chat. Getting messages from last 24h ordered by id asscending
     *
     * @param int $limit limit of messages
     * @param int $channel Channel's id
     *
     * @return array|null Array of messages or null if no messages
     */
    public function getMessagesFromLastIdAfterChangingChannel(int $limit, int $channel)
    {
        $date = new \DateTime('now');
        $date->modify( '-1 day' );

        return $this->createQueryBuilder('m')
            ->andWhere('m.date >= :date')
            ->andWhere('m.channel = :channel')
            ->orderBy('m.id', 'ASC')
            ->setParameter('channel', $channel)
            ->setParameter('date', $date)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets messages between two ids when sending new message and there was new messages
     *
     * @param int $idFirst beginning of the interval
     * @param int $idSecond End of interval
     * @param int $channel Channel's id
     *
     * @return array|null Array of messages or null if no messages
     */
    public function getMessagesBetweenIds(int $idFirst, int $idSecond, int $channel)
    {
        return $this->createQueryBuilder('m')
            ->where('m.id BETWEEN :id1 AND :id2')
            ->andWhere('m.channel = :channel')
            ->orderBy('m.id', 'ASC')
            ->setParameter('id1', $idFirst)
            ->setParameter('id2', $idSecond)
            ->setParameter('channel', $channel)
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets id of only last message on chat
     *
     * @return int message's id
     */
    public function getIdFromLastMessage()
    {
        $message =  $this->createQueryBuilder('m')
            ->orderBy('m.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
        if ($message) {
            return $message->getId();
        } else {
            return 0;
        }
    }

    /**
     * Deletes message from chat
     *
     * @param $id message's id
     *
     * @return array status of deleting
     */
    public function deleteMessage($id)
    {
        return $this->createQueryBuilder('m')
            ->delete()
            ->where('m.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

}
