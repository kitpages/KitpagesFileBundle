<?php
namespace Kitpages\FileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Kitpages\FileBundle\Model\FileManager;
use Kitpages\FileBundle\Entity\File;


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
                $fileManager->getFile($fileManager->getOriginalAbsoluteFileName($file));
            }
        }
        exit();
        return null;
    }

    public function infoAction($entityFileName)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $fileManager = $this->get('kitpages.file.manager');
        $fileClass = $fileManager->getFileClass($entityFileName);
        $fileId = $this->getRequest()->request->get('id', null);
        if (!is_null($fileId)) {
            $file = $em->getRepository($fileClass)->find($fileId);
            if ($file != null) {
                $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                $isImage = false;
                if (in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp'))) {
                    $isImage = true;
                }
                $data = array(
                    'id' => $file->getId(),
                    'fileName' => $file->getFilename(),
                    'fileExtension' => $ext,
                    'isImage' => $isImage,
                    'url' => $this->generateUrl(
                        'kitpages_file_render',
                        array(
                            'entityFileName' => $entityFileName,
                            'id' => $file->getId()
                        )
                    )
                );
                return new Response( json_encode($data) );
            }
        }
        return new Response( 'null' );
    }


}