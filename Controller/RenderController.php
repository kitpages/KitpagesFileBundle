<?php
namespace Kitpages\FileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Kitpages\FileBundle\Model\FileManager;
use Kitpages\FileBundle\Entity\File;

use Kitpages\FileSystemBundle\Model\AdapterFile;

class RenderController extends Controller
{

    public function viewAction($entityFileName){
        $fileManager = $this->get('kitpages.file.manager');
        $fileClass = $fileManager->getFileClass($entityFileName);
        $em = $this->getDoctrine()->getEntityManager();
        $fileManager = $this->get('kitpages.file.manager');
        $fileId = $this->getRequest()->query->get('id', null);
        if (!is_null($fileId)) {
            $file = $em->getRepository($fileClass)->find($fileId);
            if ($file != null) {
                $fileManager->getFileSystem()->sendFileToBrowser(
                    new AdapterFile($fileManager->getFilePath($file)),
                    $file->getFileName()
                );
            }
        }
        return null;
    }

    public function infoAction($entityFileName)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $fileManager = $this->get('kitpages.file.manager');
        $fileClass = $fileManager->getFileClass($entityFileName);
        $fileId = $this->getRequest()->request->get('id', null);
        $widthParent = $this->getRequest()->request->get('parent', null);
        if (!is_null($fileId)) {
            $file = $em->getRepository($fileClass)->find($fileId);
            if ($file != null) {
                $data = $fileManager->fileDataJson($file, $entityFileName, $widthParent);

                return new Response( json_encode($data) );
            }
        }
        return new Response( 'null' );
    }


}