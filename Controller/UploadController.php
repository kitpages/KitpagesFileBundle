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
        $this->mkdirr($dataDir);
        
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
    
    /**
     * Create a directory and all subdirectories needed.
     * @param string $pathname
     * @param octal $mode example 0666
     */
    public static function mkdirr($pathname, $mode = null)
    {
        // Check if directory already exists
        if (is_dir($pathname) || empty($pathname)) {
            return true;
        }
        // Ensure a file does not already exist with the same name
        if (is_file($pathname)) {
            return false;
        }
        // Crawl up the directory tree
        $nextPathname = substr($pathname, 0, strrpos($pathname, "/"));
        if (self::mkdirr($nextPathname, $mode)) {
            if (!file_exists($pathname)) {
                if (is_null($mode)) {
                    return mkdir($pathname);
                } else {
                    return mkdir($pathname, $mode);
                }
            }
        } else {
            throw new Exception (
                "intermediate mkdirr $nextPathname failed"
            );
        }
        return false;
    }

}
