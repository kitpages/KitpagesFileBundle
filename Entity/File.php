<?php
namespace Kitpages\FileBundle\Entity;
use Kitpages\FileBundle\Entity\FileBase;
class File extends FileBase {
    /**
     * @var Kitpages\FileBundle\Entity\FileBase
     */
    private $children;

    /**
     * @var Kitpages\FileBundle\Entity\FileBase
     */
    private $parent;

    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get children
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param Kitpages\FileBundle\Entity\FileBase $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return Kitpages\FileBundle\Entity\FileBase 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children
     *
     * @param Kitpages\FileBundle\Entity\File $children
     */
    public function addFile(\Kitpages\FileBundle\Entity\File $children)
    {
        $this->children[] = $children;
    }
}