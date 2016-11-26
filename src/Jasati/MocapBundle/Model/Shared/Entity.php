<?php

namespace Jasati\MocapBundle\Model\Shared;

use Doctrine\ORM\Mapping as ORM;

/**
 * Base object/entity
 *
 * @author Yahya <yahya6789@gmail.com>
 *
 * @ORM\MappedSuperclass
 */
abstract class Entity
{
    /**
     * Auto increment identity (PK)
     *
     * @ORM\Id
     * @ORM\GeneratedValue (strategy="AUTO")
     * @ORM\Column(type="integer", options={"unsigned":true})
     */
    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string Fully qualified class name
     */
    public static final function getClassName()
    {
        $class = new \ReflectionClass(get_called_class());
        return $class->getName();
    }
}