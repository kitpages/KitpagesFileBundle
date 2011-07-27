<?php

namespace Kitpages\FileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Kitpages\FileBundle\Model\FileManager;
use Kitpages\FileBundle\Entity\File;

class UploadController extends Controller
{
    public function checkAction()
    {
        $dataDir = $this->container->getParameter('kitpages_file.data_dir');
        $util = $this->get('kitpages.util');
        $util->mkdirr($dataDir);
        
        $fileArray = array();
        foreach ($_POST as $key => $value) {
            if ($key != 'folder') {
                if (file_exists($dataDir.'/'.$value)) {
                    $fileArray[$key] = $value;
                }
            }
        }
        return new Response( json_encode($fileArray) );
    }
    
    /**
     * @return FileManager
     */
    public function getFileManager()
    {
        return $this->get('kitpages.file.manager');
    }
    public function doUploadAction()
    {
        $fileManager = $this->getFileManager();
        $file = $fileManager->upload($_FILES['Filedata']['tmp_name'], $_FILES['Filedata']['name']);
        if ( $file instanceof File) {
            return new Response( $file->getId() );
        }
        return new Response( '0' );
    }
    
    public function uploadAction()
    {
        return $this->render('KitpagesFileBundle:Upload:upload.html.twig');
    }
    public function widgetAction($fieldId)
    {
        return $this->render(
            'KitpagesFileBundle:Upload:widget.html.twig',
            array(
                "fieldId" => $fieldId
            )
        );
    }
    
}
