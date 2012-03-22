<?php

namespace Kitpages\FileBundle\Model;

// external service
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Routing\RouterInterface;
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
    protected $router = null;
    protected $logger = null;
    protected $util = null;
    protected $dataDir = null;
    protected $publicPrefix = null;
    protected $baseUrl = null;
    protected $webRootDir = null;
    protected $entityFileList = array();
    protected $typeList = array();

    public function __construct(
        Registry $doctrine,
        EventDispatcherInterface $dispatcher,
        RouterInterface $router,
        LoggerInterface $logger,
        Util $util,
        $dataDir,
        $publicPrefix,
        $baseUrl,
        $entityFileList,
        $typeList,
        $kernelRootDir
    )
    {
        $this->dispatcher = $dispatcher;
        $this->doctrine = $doctrine;
        $this->router = $router;
        $this->logger = $logger;
        $this->util = $util;
        $this->dataDir = $dataDir;
        $this->publicPrefix = $publicPrefix;
        $this->baseUrl = $baseUrl;
        $this->entityFileList = $entityFileList;
        $this->typeList = $typeList;

        foreach($entityFileList as $entityFile => $entityFileInfo) {
            $entityClassList[$entityFile] = $entityFileInfo['class'];
        }
        $this->entityClassList = $entityClassList;

        $this->webRootDir = realpath($kernelRootDir.'/../web');
    }

    public function getFilePublicAbsoluteRootDir() {
        return $this->webRootDir.'/'.$this->publicPrefix;
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

    public function fileDataJson($file, $entityFileName, $widthParent = false) {
        $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));

        $data = array(
            'id' => $file->getId(),
            'fileName' => $file->getFilename(),
            'fileExtension' => $ext,
            'fileType' => $file->getType(),
            'isPrivate' => $file->getIsPrivate(),
            'url' => $this->router->generate(
                'kitpages_file_render',
                array(
                    'entityFileName' => $entityFileName,
                    'id' => $file->getId()
                )
            )
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

    public function renderHtml($type, $fileName) {
        $html = '';
        if($type == 'image'){
            $html = '<img class="[[file:parameter:class]]" src="[[file:url]]" />';
        } elseif ($type == 'video') {

        } elseif ($type == 'application') {
            $html = '<a class="[[file:parameter:class]]" href="[[file:url]]">'.$fileName.'</a>';
        }

        return $html;
    }

    public function createFile(
        $fileName,
        $entityFileName,
        $mimeType,
        $itemClass,
        $itemId
    ) {
        $fileClass = $this->getFileClass($entityFileName);


        $file = new $fileClass();
        $file->setItemClass($itemClass);
        $file->setItemId($itemId);
        $file->setStatus(FileInterface::STATUS_TEMP);
        $file->setFileName($fileName);
        $file->setIsPrivate(false);
        $file->setData(array());

        $typeList = explode('/', $mimeType);
        $file->setType($typeList[0]);
        $file->setMimeType($mimeType);

        $file->setHtml($this->renderHtml($typeList[0], $fileName));

        return $file;
    }

    public function createFormLocale(
        $tempFileName,
        $fileName,
        $entityFileName,
        $itemClass = null,
        $itemId = null,
        $fileParent = null,
        $publishParent = false
    ) {
        // send on event
        $event = new FileEvent();
        $event->set('tempFileName', $tempFileName);
        $event->set('fileName', $fileName);
        $event->set('dataDir', $this->getDataDirWithPrefix($entityFileName));

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tempFileName);
        finfo_close($finfo);

        // the parent file is always the original
        $file = $this->createFile($fileName, $entityFileName, $mimeType, $itemClass, $itemId);
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


    public function upload($tempFileName, $fileName, $entityFileName, $itemClass = null, $itemId = null) {
        // send on event
        $event = new FileEvent();
        $event->set('tempFileName', $tempFileName);
        $event->set('fileName', $fileName);
        $event->set('dataDir', $this->getDataDirWithPrefix($entityFileName));

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tempFileName);
        finfo_close($finfo);

        $file = $this->createFile($fileName, $entityFileName, $mimeType, $itemClass, $itemId);

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
            if (is_file($targetFileName)) {
                unlink($targetFileName);
            }
            $em = $this->getDoctrine()->getEntityManager();
            $em->remove($file);
            $em->flush();
        }
        $this->getDispatcher()->dispatch(KitpagesFileEvents::afterFileDelete, $event);
    }

    public function deleteTemp($itemCategory, $itemId, $entityFileName = 'default')
    {
        $em = $this->getDoctrine()->getEntityManager();
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
        if (!$event->isDefaultPrevented() && !$file->getIsPrivate()) {
            $targetDir = $this->getFilePublicAbsoluteDir($file);

            if (!is_file($targetDir."/".$file->getFileName())) {
                if (is_dir($targetDir)) {
                    $this->getUtil()->rmdirr($targetDir);
                }
                $this->getUtil()->mkdirr($targetDir);
                if (is_file($this->getOriginalAbsoluteFileName($file))) {
                    copy($this->getOriginalAbsoluteFileName($file), $targetDir."/".$file->getFileName() ) ;
                }
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

    public function getFilePublicAbsolute(FileInterface $file)
    {
        return $this->getFilePublicAbsoluteDir($file)."/".$file->getFileName();
    }

    public function getFilePublicAbsoluteDir(FileInterface $file)
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


    public function getFile($url, $name)
    {
        return $this->getUtil()->getFile($url, 0, null, $name);
    }

}
