<?php
namespace Kitpages\FileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Kitpages\FileBundle\Model\FileManager;
use Kitpages\FileBundle\Entity\File;


class RenderController extends Controller
{
 
    public function viewAction(){
        $fileManager = $this->get('kitpages.file.manager');
        $path = $this->getRequest()->query->get('path', null);
        if (!is_null($path)) {
            $fileManager->getFile($path);
        }
    }
    
}