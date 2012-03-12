<?php
namespace Kitpages\FileBundle\Entity;
use Kitpages\FileBundle\Entity\FileInterface;
class FileBase implements FileInterface {
//class File {

    /**
     * @var boolean $isPrivate
     */
    private $isPrivate;

    /**
     * @var string $fileName
     */
    private $fileName;

    /**
     * @var datetime $createdAt
     */
    private $createdAt;

    /**
     * @var datetime $updatedAt
     */
    private $updatedAt;

    /**
     * @var integer $id
     */
    private $id;


    /**
     * Set isPrivate
     *
     * @param boolean $isPrivate
     */
    public function setIsPrivate($isPrivate)
    {
        $this->isPrivate = $isPrivate;
    }

    /**
     * Get isPrivate
     *
     * @return boolean
     */
    public function getIsPrivate()
    {
        return $this->isPrivate;
    }

    /**
     * Set fileName
     *
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Get fileName
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get updatedAt
     *
     * @return datetime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @var boolean $hasUploadFailed
     */
    private $hasUploadFailed;

    /**
     * @var array $data
     */
    private $data;


    /**
     * Set hasUploadFailed
     *
     * @param boolean $hasUploadFailed
     */
    public function setHasUploadFailed($hasUploadFailed)
    {
        $this->hasUploadFailed = $hasUploadFailed;
    }

    /**
     * Get hasUploadFailed
     *
     * @return boolean
     */
    public function getHasUploadFailed()
    {
        return $this->hasUploadFailed;
    }

    /**
     * Set data
     *
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
    /**
     * @var string $status
     */
    private $status;


    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var string $mimeType
     */
    private $mimeType;


    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set mimeType
     *
     * @param string $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * Get mimeType
     *
     * @return string 
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }
    /**
     * @var string $renderHtml
     */
    private $renderHtml;


    /**
     * Set renderHtml
     *
     * @param string $renderHtml
     */
    public function setRenderHtml($renderHtml)
    {
        $this->renderHtml = $renderHtml;
    }

    /**
     * Get renderHtml
     *
     * @return string 
     */
    public function getRenderHtml()
    {
        return $this->renderHtml;
    }

}