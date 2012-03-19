<?php

namespace Kitpages\FileBundle\Entity;


interface FileInterface {
    const STATUS_TEMP = 'temp';
    const STATUS_VALID = 'valid';

    /**
     * Set isPrivate
     *
     * @param boolean $isPrivate
     */
    public function setIsPrivate($isPrivate);

    /**
     * Get isPrivate
     *
     * @return boolean
     */
    public function getIsPrivate();

    /**
     * Set fileName
     *
     * @param string $fileName
     */
    public function setFileName($fileName);

    /**
     * Get fileName
     *
     * @return string
     */
    public function getFileName();

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt);

    /**
     * Get createdAt
     *
     * @return datetime
     */
    public function getCreatedAt();

    /**
     * Set updatedAt
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get updatedAt
     *
     * @return datetime
     */
    public function getUpdatedAt();

    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Set hasUploadFailed
     *
     * @param boolean $hasUploadFailed
     */
    public function setHasUploadFailed($hasUploadFailed);

    /**
     * Get hasUploadFailed
     *
     * @return boolean
     */
    public function getHasUploadFailed();

    /**
     * Set data
     *
     * @param array $data
     */
    public function setData($data);

    /**
     * Get data
     *
     * @return array
     */
    public function getData();


    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status);

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type);

    /**
     * Get type
     *
     * @return string
     */
    public function getType();

    /**
     * Set mimeType
     *
     * @param string $mimeType
     */
    public function setMimeType($mimeType);

    /**
     * Get mimeType
     *
     * @return string
     */
    public function getMimeType();

    /**
     * Set html
     *
     * @param string $html
     */
    public function setHtml($html);

    /**
     * Get html
     *
     * @return string
     */
    public function getHtml();
}