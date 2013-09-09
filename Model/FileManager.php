<?php

namespace Kitpages\FileBundle\Model;

// external service
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\RouterInterface;
use Kitpages\FileBundle\Entity\File;
use Kitpages\FileBundle\Entity\FileInterface;
use Kitpages\FileBundle\Event\FileEvent;
use Kitpages\FileBundle\KitpagesFileEvents;

use Kitpages\FileSystemBundle\Service\Adapter\AdapterInterface;
use Kitpages\FileSystemBundle\Model\AdapterFile;
use Kitpages\FileSystemBundle\FileSystemException;

class FileManager {
    ////
    // dependency injection
    ////
    protected $dispatcher = null;
    /** @var EntityManager */
    protected $em = null;
    protected $router = null;
    protected $logger = null;
    protected $fileSystem = null;
    protected $tmp_dir = null;
    protected $entityFileList = array();
    protected $typeList = array();

    public function __construct(
        EntityManager $em,
        EventDispatcherInterface $dispatcher,
        RouterInterface $router,
        LoggerInterface $logger,
        AdapterInterface $fileSystem,
        $tmp_dir,
        $entityFileList,
        $typeList
    )
    {
        $this->dispatcher = $dispatcher;
        $this->em = $em;
        $this->router = $router;
        $this->logger = $logger;
        $this->fileSystem = $fileSystem;
        $this->tmpDir = $tmp_dir;
        $this->entityFileList = $entityFileList;
        $this->typeList = $typeList;

        foreach($entityFileList as $entityFile => $entityFileInfo) {
            $entityClassList[$entityFile] = $entityFileInfo['class'];
        }
        $this->entityClassList = $entityClassList;
        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0700, true);
        }


    }

    public function getFileSystem() {
        return $this->fileSystem;
    }

    public function getTmpDir() {
        return $this->tmpDir;
    }

    public function getTypeList() {
        return $this->typeList;
    }

    public function getType($type) {
        if (count($this->typeList) > 0 && isset($this->typeList[$type])) {
            return $this->typeList[$type];
        } else {
           return null;
        }
    }

    public function getEntityClassList() {
        return $this->entityClassList;
    }

    public function getActionOnFile($type, $action) {
        $typeInfo = $this->getType($type);
        return $typeInfo[$action];
    }

    public function getEntityName($file) {
        $entityFileName = array_search(get_class($file), $this->entityClassList);
        if ($entityFileName == null) {
            $entityFileName = array_search(get_parent_class($file), $this->entityClassList);
        }
        return $entityFileName;
    }



    /**
     * @return class entity
     */

    public function getEntityFile($entityFileName, $file = null)
    {
        if ($file != null) {
            $entityFileName = $this->getEntityName($file);
        }
        return $this->entityFileList[$entityFileName];
    }

    public function getFileClass($entityFileName)
    {
        $fileList = $this->entityFileList;
        $fileClass = $fileList[$entityFileName]['class'];
        return $fileClass;
    }

    public function getDataDirPrefix($entityFileName, $file = null)
    {
        if ($file != null) {
            $entityFileName = $this->getEntityName($file);
        }
        $fileList = $this->entityFileList;
        $fileDataDirPrefix = $fileList[$entityFileName]['data_dir_prefix'];
        if ($fileDataDirPrefix != null) {
            $fileDataDirPrefix = $fileDataDirPrefix.'/';
        }
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

    ////
    // actions
    ////

    public function fileDataJson($file, $entityFileName, $widthParent = false) {
        $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));

        $data = array(
            'id' => $file->getId(),
            'fileName' => $file->getFilename(),
            'fileExtension' => $ext,
            'fileType' => $file->getType(),
            'isPrivate' => $file->getIsPrivate(),
            'url' => $this->getFileLocationPrivate($file->getId(), $entityFileName)
        );
        $type = $this->getType($file->getType());
        if (count($type) > 0) {
            $data['actionList']['Action'] = $this->router->generate('kitpages_file_actionOnFile_widgetEmpty');
            foreach($type as $action=>$actionInfo) {
                if ($actionInfo['url'] != null) {
                    $data['actionList'][$action] = $this->router->generate($actionInfo['url']);
                } else {
                    $data['actionList'][$action] = $this->router->generate(
                        'kitpages_file_actionOnFile_widget',
                        array(
                            'entityFileName' => $entityFileName,
                            'typeFile' => $file->getType(),
                            'actionFile' => $action
                        )
                    );
                }
            }
        }

        if ($widthParent && $file->getParent() instanceof FileInterface) {
            $data['fileParent'] = $this->fileDataJson($file->getParent(), $entityFileName);
            $data['publishParent'] = $file->getPublishParent();
        } else {
            $data['fileParent'] = null;
            $data['publishParent'] = null;
        }

       return $data;
    }

    public function createFile(
        $fileName,
        $entityFileName,
        $mimeType,
        $itemClass,
        $itemId,
        $fileInfo
    ) {
        $fileClass = $this->getFileClass($entityFileName);


        $file = new $fileClass();
        $file->setItemClass($itemClass);
        $file->setItemId($itemId);
        $file->setStatus(FileInterface::STATUS_TEMP);
        $file->setFileName($fileName);
        $file->setIsPrivate(false);
        $file->setData($fileInfo);

        $typeList = explode('/', $mimeType);
        $file->setType($typeList[0]);
        $file->setMimeType($mimeType);

        return $file;
    }

    public function createFormLocale(
        $tempFilePath,
        $fileName,
        $entityFileName,
        $itemClass = null,
        $itemId = null,
        $fileParent = null,
        $publishParent = false
    ) {
        // send on event
        $event = new FileEvent();
        $event->set('tempFilePath', $tempFilePath);
        $event->set('fileName', $fileName);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tempFilePath);
        finfo_close($finfo);

        $fileInfo = $this->fileInfo($tempFilePath, $mimeType);

        // the parent file is always the original
        $file = $this->createFile($fileName, $entityFileName, $mimeType, $itemClass, $itemId, $fileInfo);
        if ($fileParent != null && $fileParent instanceof FileInterface  ) {
            $fileParentParent = $fileParent->getParent();
            if ($fileParentParent != null && $fileParentParent instanceof FileInterface) {
                $file->setParent($fileParentParent);
                $file->setPublishParent($publishParent);
            } else {
                $file->setParent($fileParent);
                $fileParent->setPublishParent($publishParent);
            }
        }


        $event->set('fileObject', $file);
        $this->getDispatcher()->dispatch(KitpagesFileEvents::onFileCreateFormLocale, $event);
        // default action (upload)
        if (! $event->isDefaultPrevented()) {
            // manage object creation
            $file = $event->get('fileObject');
            $em = $this->em;
            $em->persist($file);

            $em->flush();

            // manage upload
            try {
                $this->fileSystem->copyTempToAdapter(
                    $tempFilePath,
                    new AdapterFile($this->getFilePath($file))
                );
                $file->setHasUploadFailed(false);
            }
            catch (FileSystemException $e) {
                $file->setHasUploadFailed(true);
            }
            $em->flush();
        }
        // send after event
        $this->getDispatcher()->dispatch(KitpagesFileEvents::afterFileCreateFormLocale, $event);
        return $file;
    }


    public function fileInfo($file, $mimeType) {

        $fileStat = stat($file);

        $infoList['size'] = $fileStat['7'];
        $infoList['mtime'] = $fileStat['9'];

        $typeList = explode('/', $mimeType);
        if ($typeList[0] == 'image') {
            $imageInfo = getimagesize($file);
            $infoList['width'] = $imageInfo[0];
            $infoList['height'] = $imageInfo[1];
        }
        return $infoList;

    }

    public function upload($uploadFileName, $fileName, $entityFileName, $itemClass = null, $itemId = null) {
        // send on event
        $event = new FileEvent();
        $event->set('tempFileName', $uploadFileName);
        $event->set('fileName', $fileName);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $uploadFileName);
        finfo_close($finfo);

        $fileInfo = $this->fileInfo($uploadFileName, $mimeType);

        $file = $this->createFile($fileName, $entityFileName, $mimeType, $itemClass, $itemId, $fileInfo);

        $event->set('fileObject', $file);
        $this->getDispatcher()->dispatch(KitpagesFileEvents::onFileUpload, $event);
        // default action (upload)
        if (! $event->isDefaultPrevented()) {
            // manage object creation
            $file = $event->get('fileObject');
            $em = $this->em;
            $em->persist($file);

            $em->flush();

            // manage upload
            $tempFilePath = tempnam($this->getTmpDir(), $file->getId());


            move_uploaded_file($uploadFileName, $tempFilePath);
            try {
                $this->fileSystem->copyTempToAdapter(
                    $tempFilePath,
                    new AdapterFile($this->getFilePath($file)),
                    $mimeType
                );
                $file->setHasUploadFailed(false);
            }
            catch (FileSystemException $e) {
                $file->setHasUploadFailed(true);
            }
            unlink($tempFilePath);
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
            $this->fileSystem->unlink(new AdapterFile($this->getFilePath($file)));

            $em = $this->em;
            $em->remove($file);
            $em->flush();
        }
        $this->getDispatcher()->dispatch(KitpagesFileEvents::afterFileDelete, $event);
    }

    public function deleteTemp($itemCategory, $itemId, $entityFileName = 'default')
    {
        $em = $this->em;
        $fileClass = $this->getFileClass($entityFileName);
        $fileList = $em->getRepository($fileClass)->findByStatusAndItem(
            FileInterface::STATUS_TEMP,
            $itemCategory,
            $itemId
        );
        foreach($fileList as $file) {
            $this->delete($file);
        }
    }

    public function unpublish($filePath, $private)
    {
        $event = new FileEvent();
        $this->getDispatcher()->dispatch(KitpagesFileEvents::onFileUnpublish, $event);
        if (!$event->isDefaultPrevented()) {
            // remove publish file
            $this->fileSystem->unlink(new AdapterFile($filePath, $private));
            if (!$private){
                $this->fileSystem->rmdirr(new AdapterFile(dirname($filePath), $private));
            }

        }
        $this->getDispatcher()->dispatch(KitpagesFileEvents::afterFileUnpublish, $event);
    }

    public function publish(FileInterface $file)
    {
        $event = new FileEvent();
        $event->set('fileObject', $file);
        $this->getDispatcher()->dispatch(KitpagesFileEvents::onFilePublish, $event);
        if (!$event->isDefaultPrevented() && !$file->getIsPrivate()) {
            $filePublic = new AdapterFile($this->getFilePath($file, false), false);
            if (!$this->fileSystem->isFile($filePublic)) {
                $this->fileSystem->copy(
                    new AdapterFile($this->getFilePath($file)),
                    $filePublic
                );
            }

            if ($file->getPublishParent()) {
                $fileParent = $file->getParent();
                if ($fileParent instanceof FileInterface) {
                    $this->publish($fileParent);
                }
            }

        }
        $this->getDispatcher()->dispatch(KitpagesFileEvents::afterFilePublish, $event);
    }

    public function privateFileExist($nameFile) {
        return true;
    }

    public function getFilePath(FileInterface $file, $private = true)
    {
        $idString = (string) $file->getId();
        if (strlen($idString)== 1) {
            $idString = '0'.$idString;
        }
        $dir = substr($idString, 0, 2);
        $originalDir = $this->getDataDirPrefix(null, $file).$dir;

        if ($private) {
            $fileName = $originalDir.'/'.$file->getId().'-'.$file->getFilename();
            return $fileName;
        } else {
            return $originalDir.'/'.$file->getId()."/".$file->getFileName();
        }
    }

    public function getFileLocationPublic(FileInterface $file)
    {
        $private = false;
        return $this->fileSystem->getFileLocation(new AdapterFile($this->getFilePath($file, $private), $private));
    }

    public function getFileLocationPrivate($id, $entityFileName = null){

        $parameterList = array('id' => $id);
        if ($entityFileName != null) {
            $parameterList['entityFileName'] = $entityFileName;
        }
        return $this->router->generate('kitpages_file_render', $parameterList);
    }

}
