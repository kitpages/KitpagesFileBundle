<?php


namespace Kitpages\FileBundle\Form\Image;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\FormError;
use Kitpages\FileBundle\Model\FileManager;

class ResizeFormHandler
{
    protected $request;
    protected $doctrine;

    public function __construct(Registry $doctrine, Request $request, $validator, FileManager $fileManager)
    {
        $this->doctrine = $doctrine;
        $this->request = $request;
        $this->validator = $validator;
        $this->fileManager = $fileManager;
    }

    public function process(Form $form, $formFile)
    {
        $versionErrorList = array();
        if ($this->request->getMethod() == 'POST' && $this->request->request->get($form->getName()) !== null) {
            $form->bindRequest($this->request);

            if ($form->isValid()) {
                $dataForm = $this->request->request->get($formFile->getName());
                $em = $this->doctrine->getEntityManager();
            }

            foreach($versionErrorList as $level => $versionError) {
                foreach($versionError as $error) {
                    $formErrorVersion = new FormError($error->getMessage(), array('%level%' => $level));
                    $form->addError($formErrorVersion);
                }
            }
        }
        return false;
    }





    private function getRenderErrorMessages(\Symfony\Component\Form\Form $form) {
        $errorFieldList = $this->getErrorMessages($form);
        $errorHtml = '<ul>';
        foreach($errorFieldList as $errorList) {
            foreach($errorList as $error)
            $errorHtml .= '<li>'.$error.'</li>';
        }
        $errorHtml .= '</ul>';

        return $errorHtml;
    }

    private function getErrorMessages(\Symfony\Component\Form\Form $form) {
        $errors = array();
        foreach ($form->getErrors() as $key => $error) {
            $errors[] = strtr($error->getMessageTemplate(), $error->getMessageParameters());
        }
        if ($form->hasChildren()) {
            foreach ($form->getChildren() as $child) {
                if (!$child->isValid()) {
                    $errors[$child->getName()] = $this->getErrorMessages($child);
                }
            }
        }
        return $errors;
    }

}
