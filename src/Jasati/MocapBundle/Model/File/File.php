<?php

namespace Jasati\MocapBundle\Model\File;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Jasati\MocapBundle\Model\Event\Event;
use Jasati\MocapBundle\Model\Shared\Entity;
use Jasati\MocapBundle\Model\User\AbstractUser;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author Yahya <yahya6789@gmail.com>
 *
 * @ORM\Entity(repositoryClass="Jasati\MocapBundle\Model\File\FileRepository")
 * @ORM\Table(name="files")
 * @ORM\HasLifecycleCallbacks
 */

class File extends Entity
{
    //TODO: Use embedded object!

    const BINARY        = array('text/plain','application/octet-stream');
    const COMPRESSED    = array('application/zip','application/x-7z-compressed','application/x-rar-compressed');
    const VIDEO         = array('video/mp4','video/webm','video/ogg');

    /**
     * @var UploadedFile $uploadedFile
     */
    private $uploadedFile;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private $filename;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $originalFilename;

    /**
     * Absolute path to file
     *
     * @ORM\Column(name="realpath", type="string", length=255)
     *
     * @link http://php.net/manual/en/splfileinfo.getrealpath.php
     */
    private $realPath;

    /**
     * @ORM\Column(type="integer")
     */
    private $size;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $mime;

    /**
     * @ORM\Column(type="string", length=35)
     */
    private $hash;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdTime;

    /**
     * @ORM\Column(type="datetime")
     */
    private $modifiedTime;

    /**
     * @ORM\OneToMany(targetEntity="Jasati\MocapBundle\Model\Event\Event", mappedBy="file", cascade={"persist"})
     *
     * @var ArrayCollection $events
     */
    private $events;

    /**
     * @var AbstractUser
     *
     * @ORM\ManyToOne   (targetEntity="Jasati\MocapBundle\Model\User\AbstractUser")
     * @ORM\JoinColumn  (name="user_id", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Jasati\MocapBundle\Model\File\Provinsi", inversedBy="files")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     *
     * @var Provinsi $category
     */
    private $category;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    protected $roles = null;

    /**
     * @ORM\OneToMany(targetEntity="Jasati\MocapBundle\Model\File\File", mappedBy="parent",
     *      cascade={"persist","remove"})
     *
     * @var ArrayCollection $children
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Jasati\MocapBundle\Model\File\File", inversedBy="children")
     * @ORM\JoinColumn(name="bvh_id", referencedColumnName="id")
     */
    private $parent;

    public function __construct($title, UploadedFile $uploadedFile)
    {
        $this->title        = $title;
        $this->uploadedFile = $uploadedFile;
        $this->children     = new ArrayCollection();
        $this->events       = new ArrayCollection();
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getFilename($suffix = true)
    {
        if(!$suffix) {
            pathinfo($this->getRealPath(), PATHINFO_FILENAME);
        }
        return $this->filename;
    }

    public function getOriginalFilename()
    {
        return $this->originalFilename;
    }

    public function getRealPath()
    {
        return $this->realPath;
    }

    public function getSizeIn($exponent = 2, $precision = 2)
    {
        return number_format($this->getSize() / pow(1024, $exponent), $precision);
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getMime()
    {
        return $this->mime;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getCreatedTime()
    {
        return $this->createdTime;
    }

    public function getModifiedTime()
    {
        return $this->modifiedTime;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedTime()
    {
        $this->createdTime = new \DateTime();
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setModifiedTime()
    {
        $this->modifiedTime = new \DateTime();
    }

    /**
     * @return ArrayCollection
     */
    public function getEvents()
    {
        return $this->events;
    }

    public function getDownloadCount()
    {
        return count($this->getDownloadEvents());
    }

    public function getDownloadEvents()
    {
        $events = $this->getEvents();
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('type', Event::TYPE_DOWNLOAD))
        ;
        return $events->matching($criteria)->toArray();
    }

    public function getPreviewCount()
    {
        return count($this->getPreviewEvents());
    }

    public function getPreviewEvents()
    {
        $previewEvents = array();
        /** @var File $file */
        foreach($this->getChildren() as $file) {
            $events = $file->getEvents();
            $criteria = Criteria::create()
                ->where(Criteria::expr()->eq('type', Event::TYPE_PREVIEW))
            ;
            $events = $events->matching($criteria)->toArray();
            foreach ($events as $event) {
                $previewEvents[] = $event;
            }
        }
        return $previewEvents;
    }

    public function addEvent(Event $event)
    {
        $this->events->add($event);
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function addRole($role)
    {
        $this->roles[] = $role;
    }

    public function isPublic()
    {
        return (! $this->roles);
    }

    public function hasRole($role)
    {
        if($this->isPublic()) {
            return true;
        }
        foreach($this->roles as $thisRole)
        {
            if(strcasecmp($thisRole, $role) == 0) {
                return true;
            }
        }
        return false;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function getChildren($criteria = array())
    {
        $collection = new ArrayCollection($criteria);
        $_criteria = Criteria::create();

        if($collection->get('mimes')) {
            $mimes = $collection->get('mimes');
            foreach($mimes as $mime) {
                $_criteria->orWhere(Criteria::expr()->eq('mime', $mime));
            }
        }

        return $this->children->matching($_criteria)->toArray();
    }

    public function addChild(File $child)
    {
        $child->setParent($this);
        $this->children->add($child);
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function move($directory, AbstractUser $user)
    {
        $this->originalFilename = $this->uploadedFile->getClientOriginalName();
        $file = $this->uploadedFile->move($directory, $this->getRandomName($this->uploadedFile));

        $this->filename = $file->getFilename();
        $this->realPath = $file->getRealPath();
        $this->size     = $file->getSize();
        $this->user     = $user;
        $this->mime     = $file->getMimeType();
        $this->hash     = hash_file('md5', $file->getRealPath());

        $fs = new Filesystem();
        $fs->chmod($file->getRealPath(), 0700);

        /** @var File $file */
        foreach($this->getChildren() as $file) {
            $file->move($directory, $user);
        }

        /*$this->addEvent(new Event(Event::TYPE_UPLOAD, $user, $this));*/
    }

    public function remove(AbstractUser $user)
    {
        $fs = new Filesystem();

        /** @var File $file */
        foreach($this->getChildren() as $file) {
            $file->remove($user);
        }

        if($fs->exists($this->getRealPath())) {
            $fs->remove($this->getRealPath());
        }
    }

    private function getRandomName(UploadedFile $uploadedFile)
    {
        return rand(10000, 99999) . '.' . $uploadedFile->getClientOriginalExtension();
    }
}