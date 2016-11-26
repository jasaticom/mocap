<?php

namespace Jasati\MocapBundle\Model\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Yahya <yahya6789@gmail.com>
 *
 * @ORM\Entity(repositoryClass="Jasati\MocapBundle\Model\User\UserRepository")
 */
class User extends AbstractUser
{
    public function __construct($username, $name)
    {
        parent::__construct($username, $name);

        $this->roles = array('ROLE_USER');
    }
}