<?php

namespace Jasati\MocapBundle\Model\User;

use Doctrine\Common\Collections\ArrayCollection;
use Jasati\MocapBundle\Model\Shared\Repository;

/**
 * @author Yahya <yahya6789@gmail.com>
 */
class UserRepository extends Repository
{
    public function findUsers($criteria = array())
    {
        $_criteria  = new ArrayCollection($criteria);
        $page       = $_criteria->get('page');
        $limit      = $_criteria->get('limit');
        $userName   = $_criteria->get('userName');
        $userEmail  = $_criteria->get('userEmail');

        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('e')->from(AbstractUser::getClassName(),'e');

        if($userName) {
            $builder->andWhere($builder->expr()->like('e.name', ':name'));
            $builder->setParameter('name', '%'. $userName . '%');
        }

        if($userEmail) {
            $builder->orWhere($builder->expr()->like('e.email', ':email'));
            $builder->setParameter('email', "'%". $userEmail . "%'");
        }

        return $this->paginate($builder->getQuery(), $page, $limit);
    }
}