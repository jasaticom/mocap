<?php

namespace Jasati\MocapBundle\Model\Event;

use Doctrine\ORM\Mapping as ORM;
use Jasati\MocapBundle\Model\File\File;
use Jasati\MocapBundle\Model\Shared\Entity;
use Jasati\MocapBundle\Model\User\AbstractUser;

/**
 * @author Yahya <yahya6789@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="events")
 * @ORM\HasLifecycleCallbacks
 */
class Event extends Entity
{
    const TYPE_UPLOAD   = 1;
    const TYPE_DOWNLOAD = 2;
    const TYPE_PREVIEW  = 3;

    const TYPE_EDIT_PROFILE  = 4;
    const TYPE_LOGIN  = 5;
    /**
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @var AbstractUser
     *
     * @ORM\ManyToOne   (targetEntity="Jasati\MocapBundle\Model\User\AbstractUser")
     * @ORM\JoinColumn  (name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(name="event_time", type="datetime")
     */
    private $time;

    /**
     * @ORM\ManyToOne(targetEntity="Jasati\MocapBundle\Model\File\File", inversedBy="events")
     * @ORM\JoinColumn(name="file_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $file;

    public function __construct($type, AbstractUser $user = null, File $file = null)
    {
        $this->type = $type;
        $this->user = $user;
        $this->file = $file;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getTime()
    {
        return $this->time;
    }

    /**
     * @ORM\PrePersist
     */
    public function setTime()
    {
        $this->time = new \DateTime();
    }
}