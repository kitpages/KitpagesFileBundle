<?php
namespace Kitpages\FileBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Kitpages\FileBundle\Entity\FileInterface;

class FileEvent extends Event
{
    protected $data = array();
    protected $file = null;
    protected $isPrevented = false;

    public function __construct()
    {
    }

    /**
     * @Param File $file
     */
    public function setFile(FileInterface $file)
    {
        $this->file = $file;
    }
    /**
     * return File
     */
    public function getFile()
    {
        return $this->file;
    }

    public function set($key, $val)
    {
        $this->data[$key] = $val;
    }

    public function get($key)
    {
        if (!array_key_exists($key, $this->data)) {
            return null;
        }
        return $this->data[$key];
    }

    public function preventDefault()
    {
        $this->isPrevented = true;
    }

    public function isDefaultPrevented()
    {
        return $this->isPrevented;
    }
}
