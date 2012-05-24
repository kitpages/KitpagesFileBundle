<?php
namespace Kitpages\FileBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Kitpages\FileBundle\Entity\FileInterface;
use Kitpages\FileBundle\Model\FileManager;

class FileActionForm extends AbstractType
{

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
        $this->file = null;
        $this->publishParent = false;
    }

    public function setFile(FileInterface $file) {
        $this->file = $file;
    }

    public function setPublishParent($publishParent) {
        $this->publishParent = $publishParent;
    }


    public function buildForm(FormBuilder $builder, array $options)
    {
        $fileIdFieldParameter = array();
        if ($this->file != null) {
            $fileIdFieldParameter['data'] = $this->file->getId();
        }
        $publishParentFieldParameter['data'] = $this->publishParent;

        $builder->add(
            'fileId',
            'hidden',
            $fileIdFieldParameter
        );
        $builder->add(
            'publishParent',
            'hidden',
            $publishParentFieldParameter
        );
    }

    public function getName() {
        return 'KitFileFormFile';
    }

}
