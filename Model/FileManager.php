<?php

namespace Kitpages\FileBundle\Model;

// external service
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\DoctrineBundle\Registry;
use Kitpages\FileBundle\Entity\File;
use Kitpages\FileBundle\Entity\FileInterface;
use Kitpages\FileBundle\Event\FileEvent;
use Kitpages\FileBundle\KitpagesFileEvents;

use Kitpages\UtilBundle\Service\Util;


class FileManager {
    ////
    // dependency injection
    ////
    protected $dispatcher = null;
    protected $doctrine = null;
    protected $logger = null;
    protected $util = null;
    protected $dataDir = null;
    protected $publicPrefix = null;
    protected $baseUrl = null;
    protected $webRootDir = null;

    public function __construct(
        Registry $doctrine,
        EventDispatcherInterface $dispatcher,
        LoggerInterface $logger,
        Util $util,
        $dataDir,
        $publicPrefix,
        $baseUrl,
        $entityFileList,
        $kernelRootDir
    )
    {
        $this->dispatcher = $dispatcher;
        $this->doctrine = $doctrine;
        $this->logger = $logger;
        $this->util = $util;
        $this->dataDir = $dataDir;
        $this->publicPrefix = $publicPrefix;
        $this->baseUrl = $baseUrl;
        $this->entityFileList = $entityFileList;

        foreach($entityFileList as $entityFile => $entityFileInfo) {
            $entityClassList[$entityFile] = $entityFileInfo['class'];
        }
        $this->entityClassList = $entityClassList;

        $this->webRootDir = realpath($kernelRootDir.'/../web');
    }

    public function getEntityName($file) {
        $entityFileName = array_search(get_class($file), $this->entityClassList);
        if ($entityFileName == null) {
            $entityFileName = array_search(get_parent_class($file), $this->entityClassList);
        }
        return $entityFileName;
    }

    public function getDataDirWithPrefix($entityFileName, $file = null) {
        if ($file != null) {
            $entityFileName = $this->getEntityName($file);
        }
        $prefix = $this->getDataDirPrefix($entityFileName);
        return $this->dataDir.$prefix;
    }



    /**
     * @return class entity
     */
    public function getFileClass($entityFileName)
    {
        $fileList = $this->entityFileList;
        $fileClass = $fileList[$entityFileName]['class'];
        return $fileClass;
    }

    public function getDataDirPrefix($entityFileName)
    {
        $fileList = $this->entityFileList;
        $fileDataDirPrefix = $fileList[$entityFileName]['data_dir_prefix'];
        return $fileDataDirPrefix;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return EventDispatcherInterface $dispatcher
     */
    public function getDispatcher() {
        return $this->dispatcher;
    }

    /**
     * @return Registry
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }
    /**
     * @return Util
     */
    public function getUtil()
    {
        return $this->util;
    }

    ////
    // actions
    ////

    public function createFile($fileName, $entityFileName) {
        $fileClass = $this->getFileClass($entityFileName);
        $file = new $fileClass();
        $file->setStatus(FileInterface::STATUS_TEMP);
        $file->setFileName($fileName);
        $file->setIsPrivate(false);
        $file->setData(array());
        return $file;
    }

    public function createFormLocale($tempFileName, $fileName, $entityFileName) {
        // send on event
        $event = new FileEvent();
        $event->set('tempFileName', $tempFileName);
        $event->set('fileName', $fileName);
        $event->set('dataDir', $this->getDataDirWithPrefix($entityFileName));

        $file = $this->createFile($fileName, $entityFileName);

        $event->set('fileObject', $file);
        $this->getDispatcher()->dispatch(KitpagesFileEvents::onFileCreateFormLocale, $event);
        // default action (upload)
        if (! $event->isDefaultPrevented()) {
            // manage object creation
            $file = $event->get('fileObject');
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($file);

            $em->flush();

            // manage upload
            $targetFileName = $this->getOriginalAbsoluteFileName($file);
            $originalDir = dirname($targetFileName);
            $this->getUtil()->mkdirr($originalDir);

            if (rename($tempFileName,$targetFileName)) {
                $file->setHasUploadFailed(false);
            }
            else {
                $file->setHasUploadFailed(true);
            }
            $em->flush();
        }
        // send after event
        $this->getDispatcher()->dispatch(KitpagesFileEvents::afterFileCreateFormLocale, $event);
        return $file;
    }


    public function upload($tempFileName, $fileName, $entityFileName) {
        // send on event
        $event = new FileEvent();
        $event->set('tempFileName', $tempFileName);
        $event->set('fileName', $fileName);
        $event->set('dataDir', $this->getDataDirWithPrefix($entityFileName));

        $file = $this->createFile($fileName, $entityFileName);

        $event->set('fileObject', $file);
        $this->getDispatcher()->dispatch(KitpagesFileEvents::onFileUpload, $event);
        // default action (upload)
        if (! $event->isDefaultPrevented()) {
            // manage object creation
            $file = $event->get('fileObject');
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($file);

            $em->flush();

            // manage upload
            $targetFileName = $this->getOriginalAbsoluteFileName($file);
            $originalDir = dirname($targetFileName);
            $this->getUtil()->mkdirr($originalDir);

            if (move_uploaded_file($tempFileName, $targetFileName)) {
                $file->setHasUploadFailed(false);
            }
            else {
                $file->setHasUploadFailed(true);
            }
            $em->flush();
        }
        // send after event
        $this->getDispatcher()->dispatch(KitpagesFileEvents::afterFileUpload, $event);
        return $file;
    }

    public function delete(FileInterface $file)
    {
        $event = new FileEvent();
        $event->set('fileObject', $file);
        $this->getDispatcher()->dispatch(KitpagesFileEvents::onFileDelete, $event);
        if (!$event->isDefaultPrevented()) {
            // remove original file
            $targetFileName = $this->getOriginalAbsoluteFileName($file);
            $originalDir = dirname($targetFileName);

            if (is_dir($originalDir)) {
                $this->getUtil()->rmdirr($originalDir);
            }
            $em = $this->getDoctrine()->getEntityManager();
            $em->remove($file);
            $em->flush();
        }
        $this->getDispatcher()->dispatch(KitpagesFileEvents::afterFileDelete, $event);
    }

    public function unpublish($dir)
    {
        $event = new FileEvent();
        $this->getDispatcher()->dispatch(KitpagesFileEvents::onFileUnpublish, $event);
        if (!$event->isDefaultPrevented()) {
            $targetDir = $this->webRootDir.'/'.$dir;
            // remove publish file
            if (is_dir($targetDir)) {
                $this->getUtil()->rmdirr($targetDir);
            }
        }
        $this->getDispatcher()->dispatch(KitpagesFileEvents::afterFileUnpublish, $event);
    }

    public function publish(FileInterface $file)
    {
        $event = new FileEvent();
        $event->set('fileObject', $file);
        $this->getDispatcher()->dispatch(KitpagesFileEvents::onFilePublish, $event);
        if (!$event->isDefaultPrevented()) {
            $targetDir = $this->getAbsoluteFilePublic($file);
            if (is_dir($targetDir)) {
                $this->getUtil()->rmdirr($targetDir);
            }
            $this->getUtil()->mkdirr($targetDir);

            // copy original file
            if (is_file($this->getOriginalAbsoluteFileName($file))) {
                copy($this->getOriginalAbsoluteFileName($file), $targetDir."/".$file->getFileName() ) ;
            }
            // copy generated files
            foreach (glob($this->getGenerationDir($file).'/*') as $fileName) {
                if (is_file($fileName)) {
                    copy($fileName, $targetDir."/".$file->getFileName());
                }
            }
        }
        $this->getDispatcher()->dispatch(KitpagesFileEvents::afterFilePublish, $event);
    }

    public function getOriginalAbsoluteFileName(FileInterface $file)
    {
        $idString = (string) $file->getId();
        if (strlen($idString)== 1) {
            $idString = '0'.$idString;
        }
        $dir = substr($idString, 0, 2);
        // manage upload
        $originalDir = $this->getDataDirWithPrefix(null, $file).'/original/'.$dir;
        $fileName = $originalDir.'/'.$file->getId().'-'.$file->getFilename();
        return $fileName;
    }


    public function getGenerationDir(FileInterface $file)
    {
        $idString = (string) $file->getId();
        if (strlen($idString)== 1) {
            $idString = '0'.$idString;
        }
        $dir = substr($idString, 0, 2);
        $generationDir = $this->getDataDirWithPrefix(null, $file).'/generated/'.$dir.'/'.$file->getId();
        $this->getUtil()->mkdirr($generationDir);
        return $generationDir;
    }

    public function getAbsoluteFilePublic(FileInterface $file)
    {
        $idString = (string) $file->getId();
        if (strlen($idString)== 1) {
            $idString = '0'.$idString;
        }
        $dir = substr($idString, 0, 2);
        $entityName = $this->getEntityName($file);
        return $this->webRootDir.'/'.$this->publicPrefix.$this->entityFileList[$entityName]['data_dir_prefix'].'/'.$dir.'/'.$file->getId();
    }

    public function getFilePublicLocation(FileInterface $file)
    {
        $idString = (string) $file->getId();
        if (strlen($idString)== 1) {
            $idString = '0'.$idString;
        }
        $dir = substr($idString, 0, 2);
        $entityName = $this->getEntityName($file);
        return $this->baseUrl.'/'.$this->publicPrefix.$this->entityFileList[$entityName]['data_dir_prefix'].'/'.$dir.'/'.$file->getId();
    }

    public function getFileLocation($id){
        return $this->baseUrl."/file/render?id=".$id;
    }


    public function getFile($url)
    {
        return $this->getUtil()->getFile($url, 0);
    }

}

?>
