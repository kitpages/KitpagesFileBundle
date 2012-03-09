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

    public function widgetAction($typeFile, $actionFile) {
        $fileId = $this->getRequest()->query->get("id", null);

        $fileManager = $this->get('kitpages.file.manager');
        $action = $fileManager->getActionOnFile($typeFile, $actionFile);

        $formFile = $this->container->get($action['form']);
        $formFile->setFileId($fileId);
        $form   = $this->createForm($formFile);

//        $formHandler = $this->container->get($action['handler_form']);
//        $process = $formHandler->process($form, $formFile);
//
//        if ($process) {
////            $target = $request->query->get('kitpages_target', null);
////            if ($target) {
////                return $this->redirect($target);
////            }
//            return new Response();
//        }

        return $this->render(
            $action['form_twig'],
            array(
                'form' => $form->createView(),
                'typeFile'=>$typeFile,
                'actionFile'=>$actionFile
            )
        );
    }

    public function doAction($typeFile, $actionFile) {
        $fileId = $this->getRequest()->query->get("id", null);

        $fileManager = $this->get('kitpages.file.manager');
        $action = $fileManager->getActionOnFile($typeFile, $actionFile);

        $formFile = $this->container->get($action['form']);
        $formFile->setFileId($fileId);
        $form   = $this->createForm($formFile);

        $formHandler = $this->container->get($action['handler_form']);
        $process = $formHandler->process($form, $formFile);

        if ($process) {
//            $target = $request->query->get('kitpages_target', null);
//            if ($target) {
//                return $this->redirect($target);
//            }
            return new Response();
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
//            $form->bindRequest($request);
//            if ($form->isValid()) {
//                $data = $form->getData();
//                  return new Response();
//            }
//            return new Response();
//        }
//
//    }

}