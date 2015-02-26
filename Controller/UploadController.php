<?php

namespace Kitpages\FileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Kitpages\FileBundle\Model\FileManager;
use Kitpages\FileBundle\Entity\FileInterface;

class UploadController extends Controller
{

    public $defaultParameterList = array(
        'buttonText' => 'Upload a File',
        'multi' => false,
        'publishParent' => false
    );

    public function checkAction()
    {
        return new Response();
    }

    /**
     * @return FileManager
     */
    protected function getFileManager()
    {
        return $this->get('kitpages.file.manager');
    }
    public function doUploadAction($entityFileName, $itemClass, $itemId)
    {
        $logger = $this->get('logger');
        $logger->debug("************ FileBundle::DoUpload");
        $fileManager = $this->getFileManager();
        $file = $fileManager->upload(
            $_FILES['Filedata']['tmp_name'],
            $_FILES['Filedata']['name'],
            $entityFileName,
            $itemClass,
            $itemId
        );
        if ( $file instanceof FileInterface) {
            $data = $fileManager->fileDataJson($file, $entityFileName);

            return new Response( json_encode($data) );
        }
        return new Response( '0' );
    }

    public function widgetAction(
        $fieldId,
        $entityFileName = 'default',
        $itemClass = null,
        $itemId = null,
        $parameterList = array(),
        $render = 'KitpagesFileBundle:Upload:widget.html.twig'
    )
    {
        $parameterList = array_merge($this->defaultParameterList, $parameterList);

        return $this->render(
            $render,
            array(
                "fieldId" => $fieldId,
                "entityFileName" => $entityFileName,
                "itemClass" => $itemClass,
                "itemId" => $itemId,
                "parameterList" => $parameterList,
                "kitpages_file_session_id" => session_id()
            )
        );
    }

    public function collectionWidgetAction($entityFileName = 'default', $parameterList = array())
    {
        $parameterList = array_merge($this->defaultParameterList, $parameterList);

        return $this->render(
            'KitpagesFileBundle:Upload:widgetJs.html.twig',
            array(
                "entityFileName" => $entityFileName,
                "parameterList" => $parameterList,
                "kitpages_file_session_id" => session_id()
            )
        );
    }
}
