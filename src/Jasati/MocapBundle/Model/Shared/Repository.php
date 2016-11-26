<?php

namespace Jasati\MocapBundle\Model\Shared;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @author Yahya <yahya6789@gmail.com>
 */
abstract class Repository extends EntityRepository
{
    public function persist(Entity $entity)
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }

    public function persistAll(array $entities)
    {
        $em = $this->getEntityManager();
        foreach($entities as $entity) {
            $em->persist($entity);
        }
        $em->flush();
    }

    public function remove(Entity $entity)
    {
        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();
    }

    public function update(Entity $entity)
    {
        $em = $this->getEntityManager();
        $em->merge($entity);
        $em->flush();
    }

    protected function paginate(Query $query, $page = 0, $limit = 0)
    {
        $paginator = new Paginator($query);
        if($page) {
            $paginator->getQuery()
                ->setFirstResult(($limit * ($page - 1)))
                ->setMaxResults($limit);
        }
        return $paginator;
    }
}