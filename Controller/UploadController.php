<?php

namespace Kitpages\FileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Kitpages\FileBundle\Model\FileManager;
use Kitpages\FileBundle\Entity\FileInterface;

class UploadController extends Controller
{

    public $defaultParameterList = array(
        'buttonText' => 'Upload a File'
    );

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
    public function doUploadAction($entityFileName)
    {
        $fileManager = $this->getFileManager();
        $file = $fileManager->upload($_FILES['Filedata']['tmp_name'], $_FILES['Filedata']['name'], $entityFileName);
        if ( $file instanceof FileInterface) {
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
                        'id' => $file->getId(),
                        'entityFileName' => $entityFileName
                    )
                )
            );
            return new Response( json_encode($data) );
        }
        return new Response( '0' );
    }

    public function uploadAction()
    {
        return $this->render('KitpagesFileBundle:Upload:upload.html.twig');
    }
    public function widgetAction($fieldId, $entityFileName = 'default', $parameterList = array())
    {

        $parameterList = array_merge($this->defaultParameterList, $parameterList);

        return $this->render(
            'KitpagesFileBundle:Upload:widget.html.twig',
            array(
                "fieldId" => $fieldId,
                "entityFileName" => $entityFileName,
                "parameterList" => $parameterList,
                "kitpages_file_session_id" => session_id()
            )
        );
    }

    public function collectionWidgetAction($entityFileName = 'default', $parameterList = array())
    {

        $parameterList = array_merge($this->defaultParameterList, $parameterList);

        return $this->render(
            'KitpagesFileBundle:Upload:collectionWidget.html.twig',
            array(
                "entityFileName" => $entityFileName,
                "parameterList" => $parameterList,
                "kitpages_file_session_id" => session_id()
            )
        );
    }
}
