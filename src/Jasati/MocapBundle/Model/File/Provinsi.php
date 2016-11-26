<?php

namespace Jasati\MocapBundle\Model\File;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Jasati\MocapBundle\Model\Shared\Entity;

/**
 * @author Yahya <yahya6789@gmail.com>
 *
 * @ORM\Entity(repositoryClass="Jasati\MocapBundle\Model\File\FileRepository")
 */
class Provinsi extends Entity
{
    /**
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Jasati\MocapBundle\Model\File\File", mappedBy="category")
     *
     * @var ArrayCollection $files
     */
    private $files;

    public function __construct($name)
    {
        $this->name     = $name;
        $this->files    = new ArrayCollection();
    }

    public function getName()
    {
        return $this->name;
    }

    public function addFile(File $file)
    {
        $this->files->add($file);
    }

    public function getFiles()
    {
        return $this->files->toArray();
    }
}