<?php

namespace Ml\ServiceBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * BasicUserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BasicUserRepository extends EntityRepository
{
    public function findByOwner($user) {
        $reqRes = $this->createQueryBuilder("b")->leftJoin('b.basic', 's')->where('s.user=:user')->setParameter('user', $user);
        
        return $reqRes->getQuery()->getResult();;
    }
}
