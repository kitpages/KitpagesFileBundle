<?php


namespace Kitpages\FileBundle\Form\Image;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\FormError;

use Imagine\Image\Box;
use Imagine\Filter\Transformation;

use Kitpages\FileBundle\Model\FileManager;
use Kitpages\FileSystemBundle\Model\AdapterFile;

class ResizeFormHandler
{
    protected $request;
    protected $doctrine;

    public function __construct(
        Registry $doctrine,
        Request $request,
        $validator,
        FileManager $fileManager,
        $library
    )
    {
        $this->doctrine = $doctrine;
        $this->request = $request;
        $this->validator = $validator;
        $this->fileManager = $fileManager;
        $this->library = $library;
    }

    public function process(Form $form, $formFile, $entityFileName)
    {
        $versionErrorList = array();
        if ($this->request->getMethod() == 'POST' && $this->request->request->get($form->getName()) !== null) {
            $form->bindRequest($this->request);

            if ($form->isValid()) {
                $dataForm = $this->request->request->get($formFile->getName());
                $fileClass = $this->fileManager->getFileClass($entityFileName);
                $fileId = $dataForm['fileId'];
                $publishParent = $dataForm['publishParent'];
                if (!is_null($fileId)) {
                    $em = $this->doctrine->getEntityManager();
                    $file = $em->getRepository($fileClass)->find($fileId);


                    $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                    $tmpFileName = tempnam($this->fileManager->getTmpDir(), $fileId);

                    $imagine = $this->library;
                    $image = $imagine->load(
                        $this->fileManager->getFileSystem()->getFileContent(
                            new AdapterFile($this->fileManager->getFilePath($file))
                        )
                    );
                    $size  = new Box($dataForm['width'], $dataForm['height']);
                    $transformation = new Transformation();
                    $transformation->resize($size)
                        ->save($tmpFileName.'.'.$ext)
                        ->apply($image);

                    $fileVersion = $this->fileManager->createFormLocale(
                        $tmpFileName.'.'.$ext,
                        $file->getFileName(),
                        $entityFileName,
                        $file->getItemClass(),
                        $file->getItemId(),
                        $file,
                        $publishParent
                    );
                    unlink($tmpFileName);
                    return $fileVersion;
                }

            }
            foreach($versionErrorList as $level => $versionError) {
                foreach($versionError as $error) {
                    $formErrorVersion = new FormError($error->getMessage(), array('%level%' => $level));
                    $form->addError($formErrorVersion);
                }
            }
        }
        return null;
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
