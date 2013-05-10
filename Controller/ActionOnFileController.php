<?php
namespace Kitpages\FileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Kitpages\FileBundle\Model\FileManager;
use Kitpages\FileBundle\Entity\File;
use Kitpages\FileBundle\Form\ResizeImageForm;

class ActionOnFileController extends Controller
{
    public function widgetEmptyAction() {
        return new Response();
    }

    public function widgetAction($entityFileName, $typeFile, $actionFile) {
        $fileId = $this->getRequest()->request->get("id", null);
        $publishParent = $this->getRequest()->request->get("publishParent", null);
        $fileManager = $this->get('kitpages.file.manager');

        $fileClass = $fileManager->getFileClass($entityFileName);
        $em = $em = $this->getDoctrine()->getManager();
        $file = $em->getRepository($fileClass)->find($fileId);


        $action = $fileManager->getActionOnFile($typeFile, $actionFile);

        $formFile = $this->container->get($action['form']);

        $formFile->setFile($file);
        $formFile->setPublishParent($publishParent);
        $form   = $this->createForm($formFile);

        return $this->render(
            $action['form_twig'],
            array(
                'form' => $form->createView(),
                'entityFileName'=>$entityFileName,
                'typeFile'=>$typeFile,
                'actionFile'=>$actionFile
            )
        );
    }

    public function doAction($entityFileName, $typeFile, $actionFile) {
        $fileManager = $this->get('kitpages.file.manager');
        $action = $fileManager->getActionOnFile($typeFile, $actionFile);

        $formFile = $this->container->get($action['form']);
        $form   = $this->createForm($formFile);

        $formHandler = $this->container->get($action['handler_form']);
        $fileVersion = $formHandler->process($form, $formFile, $entityFileName);

        if (!is_null($fileVersion)) {
            $data = $fileManager->fileDataJson($fileVersion, $entityFileName, true);
            return new Response( json_encode($data) );
        }
        return new Response();
    }

//    public function widgetResizeImageAction() {
//        $fileId = $this->getRequest()->query->get("id", null);
//
//        // build basic form
//        $builder = $this->createFormBuilder();
//
//        $resizeForm = new ResizeImageForm();
//        $resizeForm->setFileId($fileId);
//        $form   = $this->createForm($resizeForm);
//
//        return $this->render(
//            'KitpagesFileBundle:ActionOnFile:widgetResizeImage.html.twig',
//            array(
//                'form' => $form->createView()
//            )
//        );
//    }
//
//    public function doResizeImageAction() {
//
//        $request = $this->getRequest();
//        $form   = $this->createForm(new ResizeImageForm());
//
//        if ($request->getMethod() == 'POST') {
//            $form->bind($request);
//            if ($form->isValid()) {
//                $data = $form->getData();
//                  return new Response();
//            }
//            return new Response();
//        }
//
//    }

}