<?php
namespace Kitpages\FileBundle\Form\Image;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Kitpages\FileBundle\Form\FileActionForm;

class ResizeForm extends FileActionForm
{

    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $ratioFieldParameter = array('label'=>'keep the ratio', 'required' => false);
        $widthFieldParameter = array('label'=>'Width', 'required' => false);
        $heightFieldParameter = array("label" => "Height", 'required' => false);
        if ($this->file != null) {
            $fileInfo = getimagesize($this->fileManager->getOriginalAbsoluteFileName($this->file));
            $widthFieldParameter['data'] = $fileInfo[0];
            $heightFieldParameter['data'] = $fileInfo[1];
            $ratioFieldParameter['data'] = true;
        }

        $builder->add(
            'ratio',
            'checkbox',
            $ratioFieldParameter
        );

        $builder->add(
            'width',
            'text',
            $widthFieldParameter
        );
        $builder->add(
            'height',
            'text',
            $heightFieldParameter
        );
    }

    public function getName() {
        return 'KitFileFormResizeImage';
    }

}
