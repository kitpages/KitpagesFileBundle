<?php

namespace Kitpages\FileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UploadController extends Controller
{
    public function checkAction()
    {
        $fileArray = array();
        foreach ($_POST as $key => $value) {
            if ($key != 'folder') {
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . $_POST['folder'] . '/' . $value)) {
                    $fileArray[$key] = $value;
                }
            }
        }
        echo json_encode($fileArray);
        return;
        //return $this->render('KitpagesFileBundle:Default:index.html.twig');
    }
    
    public function doUploadAction()
    {
        if (!empty($_FILES)) {
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $targetPath = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
            $targetFile =  str_replace('//','/',$targetPath) . $_FILES['Filedata']['name'];

            // $fileTypes  = str_replace('*.','',$_REQUEST['fileext']);
            // $fileTypes  = str_replace(';','|',$fileTypes);
            // $typesArray = split('\|',$fileTypes);
            // $fileParts  = pathinfo($_FILES['Filedata']['name']);

            // if (in_array($fileParts['extension'],$typesArray)) {
                // Uncomment the following line if you want to make the directory if it doesn't exist
                // mkdir(str_replace('//','/',$targetPath), 0755, true);

                move_uploaded_file($tempFile,$targetFile);
                echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$targetFile);
            // } else {
            // 	echo 'Invalid file type.';
            // }
        }
    }
}
