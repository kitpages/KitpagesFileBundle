<?php
namespace Kitpages\FileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Kitpages\FileBundle\Model\FileManager;
use Kitpages\FileBundle\Entity\File;


class RenderController extends Controller
{

    public function htmlWidgetAction($fileInfo, $parameterList = array()){

        $resultingHtml = $fileInfo['html'];
        foreach ($parameterList as $paramName => $paramValue) {
            $resultingHtml = str_replace("[[file:parameter:$paramName]]", $paramValue, $resultingHtml);
        }
        if ($fileInfo['type'] == 'image' || $fileInfo['type'] == 'application') {
            $resultingHtml = str_replace("[[file:url]]", $fileInfo['url'], $resultingHtml);
        }
        $resultingHtml = preg_replace("#\[\[file:(.+)\]\]#", '', $resultingHtml);

        return new Response($resultingHtml);
    }

    public function viewAction($entityFileName){
        $fileManager = $this->get('kitpages.file.manager');
        $fileClass = $fileManager->getFileClass($entityFileName);
        $em = $this->getDoctrine()->getEntityManager();
        $fileManager = $this->get('kitpages.file.manager');
        $fileId = $this->getRequest()->query->get('id', null);
        if (!is_null($fileId)) {
            $file = $em->getRepository($fileClass)->find($fileId);
            if ($file != null) {
                $fileManager->getFile(
                    $fileManager->getOriginalAbsoluteFileName($file),
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