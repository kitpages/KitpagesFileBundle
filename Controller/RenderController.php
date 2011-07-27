<?php
namespace Kitpages\FileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Kitpages\FileBundle\Model\FileManager;
use Kitpages\FileBundle\Entity\File;


class RenderController extends Controller
{
 
    public function viewAction(){
        $em = $this->getDoctrine()->getEntityManager();
        $fileManager = $this->get('kitpages.file.manager');
        $fileId = $this->getRequest()->query->get('id', null);
        if (!is_null($fileId)) {
            $file = $em->getRepository('KitpagesFileBundle:File')->find($fileId);
            if ($file != null) {
                $fileManager->getFile($fileManager->getOriginalAbsoluteFileName($file));
            }
        }
    }
    
}