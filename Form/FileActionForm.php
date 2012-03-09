<?php
namespace Kitpages\FileBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FileActionForm extends AbstractType
{

    public function __construct()
    {
        $this->fileId = '';
    }

    public function setFileId($fileId) {
        $this->fileId = $fileId;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add(
            'fileId',
            'hidden',
            array(
                'required' => true,
                'data' => $this->fileId
            )
        );

    }

    public function getName() {
        return 'KitFileFormFile';
    }

}
