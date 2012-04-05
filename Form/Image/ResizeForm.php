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
            $fileInfo = $this->file->getData();
            if (isset($fileInfo['width'])) {
                $widthFieldParameter['data'] = $fileInfo['width'];
            }
            if (isset($fileInfo['height'])) {
                $heightFieldParameter['data'] = $fileInfo['height'];
            }
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
