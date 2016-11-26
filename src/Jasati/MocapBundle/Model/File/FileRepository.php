<?php

namespace Jasati\MocapBundle\Model\File;

use Doctrine\Common\Collections\ArrayCollection;
use Jasati\MocapBundle\Model\Shared\Repository;

/**
 * @author Yahya <yahya6789@gmail.com>
 */
class FileRepository extends Repository
{
    public function findCategories(array $_criteria = array())
    {
        $criteria   = new ArrayCollection($_criteria);
        $page       = $criteria->get('page');
        $limit      = $criteria->get('limit');

        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('e')->from(Provinsi::getClassName(),'e');
        $builder->addOrderBy('e.id');

        return $this->paginate($builder->getQuery(), $page, $limit);
    }

    public function findFiles($entityName, $criteria = array())
    {
        $_criteria  = new ArrayCollection($criteria);
        $page       = $_criteria->get('page');
        $limit      = $_criteria->get('limit');
        $title      = $_criteria->get('title');
        $category   = $_criteria->get('category');
        $mimes      = $_criteria->get('mimes');

        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('e')->from($entityName,'e');

        if($title) {
            $builder->andWhere($builder->expr()->like('e.title', "'%". $title . "%'"));
        }

        if($category) {
            $builder->andWhere($builder->expr()->eq('e.category', ':category'));
            $builder->setParameter('category', $category);
        }

        if($mimes) {
            $expr = array();
            foreach($mimes as $id => $keyword) {
                $expr[] = $builder->expr()->eq('e.mime', ":mime_".$id);
                $builder->setParameter("mime_".$id, $keyword);
            }
            $builder->andWhere($builder->expr()->orX()->addMultiple($expr));
        }

        return $this->paginate($builder->getQuery(), $page, $limit);
    }
}