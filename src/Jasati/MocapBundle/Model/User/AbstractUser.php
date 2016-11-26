<?php

namespace Jasati\MocapBundle\Model\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Jasati\MocapBundle\Model\Event\Event;
use Jasati\MocapBundle\Model\Shared\Entity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Yahya <yahya6789@gmail.com>
 *
 * @ORM\Entity(repositoryClass="Jasati\MocapBundle\Model\User\UserRepository")
 * @ORM\Table(name="users")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator", type="smallint")
 * @ORM\DiscriminatorMap({
 *      1 = "Jasati\MocapBundle\Model\User\User",
 *      2 = "Jasati\MocapBundle\Model\User\Admin"
 * })
 * @ORM\HasLifecycleCallbacks
 */
abstract class AbstractUser extends Entity implements UserInterface, \Serializable
{
    /**
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $name;

    /**
     * @ORM\Column(type="simple_array")
     */
    protected $roles;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdTime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $modifiedTime;

    /**
     * @ORM\OneToMany(targetEntity="Jasati\MocapBundle\Model\Event\Event", mappedBy="user", cascade={"persist"})
     *
     * @var ArrayCollection $events
     */
    private $events;

    public function __construct($email, $name)
    {
        $this->email = $email;
        $this->name  = $name;
        $this->events = new ArrayCollection();
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function addRole($role)
    {
        $this->roles[] = $role;
    }

    public function isAdmin()
    {
        return in_array('ROLE_ADMIN', $this->getRoles());
    }

    public function getCreatedTime()
    {
        return $this->createdTime;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedTime()
    {
        $this->createdTime = new \DateTime();
    }

    public function getModifiedTime()
    {
        return $this->modifiedTime;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setModifiedTime()
    {
        $this->modifiedTime = new \DateTime();
    }

    public function changePassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return ArrayCollection
     *
     * @param array $criteria
     * @return ArrayCollection
     */
    public function getEvents($criteria = array())
    {
        $collection = new ArrayCollection($criteria);
        $criteria = Criteria::create();

        if($collection->get('orderby')) {
            $criteria->orderBy(array(
                $collection->get('orderby') => $collection->get('ordering'))
            );
        }

        if($collection->get('page')) {
            $criteria->setFirstResult($collection->get('page'));
        }

        if($collection->get('limit')) {
            $criteria->setMaxResults($collection->get('limit'));
        }

        return $this->events->matching($criteria);
    }

    public function addEvent(Event $event)
    {
        $this->events->add($event);
    }

    public function addLoginEvent()
    {
        $this->addEvent(new Event(Event::TYPE_LOGIN, $this, null));
    }

    public function addEditProfileEvent()
    {
        $this->addEvent(new Event(Event::TYPE_EDIT_PROFILE, $this, null));
    }

    public function getLastEvent()
    {
        return $this->getEvents(array('orderby' => 'time', 'limit' => 1))->get(0);
    }

    public function getDownloadCount()
    {
        return count($this->getDownloadEvents());
    }

    public function getDownloadEvents()
    {
        $events = $this->getEvents();
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('user', $this))
            ->andWhere(Criteria::expr()->eq('type', Event::TYPE_DOWNLOAD))
        ;
        return $events->matching($criteria)->toArray();
    }

    public function getSalt()
    {
        return;
    }

    public function eraseCredentials()
    {
        return;
    }

    public function serialize()
    {
        return serialize(array(
            $this->getId(),
            $this->getUsername(),
            $this->getName(),
            $this->getPassword(),
            $this->getRoles(),
        ));
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->name,
            $this->password,
            $this->roles
        ) = unserialize($serialized);
    }
}