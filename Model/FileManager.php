<?php

namespace Kitpages\FileBundle\Model;

// external service
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\DoctrineBundle\Registry;

use Kitpages\FileBundle\Entity\File;
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
        $this->webRootDir = realpath($kernelRootDir.'/../web');
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
    public function upload($tempFileName, $fileName) {
        $log = $this->getLogger();
        // send on event
        $event = new FileEvent();
        $event->set('tempFileName', $tempFileName);
        $event->set('fileName', $fileName);
        $event->set('dataDir', $this->dataDir);
        $file = new File();
        $file->setStatus(File::STATUS_TEMP);
        $file->setFileName($fileName);
        $file->setIsPrivate(false);
        $file->setData(array());
        $event->set('fileObject', $file);
        $this->getDispatcher()->dispatch(KitpagesFileEvents::onFileUpload, $event);
        // default action (upload)
        if (! $event->isDefaultPrevented()) {
            // manage object creation
            $file = $event->get('fileObject');
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($file);
            $this->getLogger()->info('file saved with id='.$file->getId());

            $em->flush();
            // manage upload
            $targetFileName = $this->getOriginalAbsoluteFileName($file);
            $originalDir = dirname($targetFileName);
            $this->getUtil()->mkdirr($originalDir);

            $log->info("start doUpload, $tempFileName => $fileName => $originalDir");
            if (move_uploaded_file($tempFileName,$targetFileName)) {
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

    public function delete(File $file)
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

    public function publish(File $file)
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

    public function getOriginalAbsoluteFileName(File $file)
    {
        $idString = (string) $file->getId();
        if (strlen($idString)== 1) {
            $idString = '0'.$idString;
        }
        $dir = substr($idString, 0, 2);
        // manage upload
        $originalDir = $this->dataDir.'/original/'.$dir;
        $fileName = $originalDir.'/'.$file->getId().'-'.$file->getFilename();
        return $fileName;
    }


    public function getGenerationDir(File $file)
    {
        $idString = (string) $file->getId();
        if (strlen($idString)== 1) {
            $idString = '0'.$idString;
        }
        $dir = substr($idString, 0, 2);
        $generationDir = $this->dataDir.'/generated/'.$dir.'/'.$file->getId();
        $this->getUtil()->mkdirr($generationDir);
        return $generationDir;
    }

    public function getAbsoluteFilePublic(File $file)
    {
        $idString = (string) $file->getId();
        if (strlen($idString)== 1) {
            $idString = '0'.$idString;
        }
        $dir = substr($idString, 0, 2);
        return $this->webRootDir.'/'.$this->publicPrefix.'/'.$dir.'/'.$file->getId();
    }

    public function getFilePublicLocation(File $file)
    {
        $idString = (string) $file->getId();
        if (strlen($idString)== 1) {
            $idString = '0'.$idString;
        }
        $dir = substr($idString, 0, 2);
        return $this->baseUrl.'/'.$this->publicPrefix.'/'.$dir.'/'.$file->getId();
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
