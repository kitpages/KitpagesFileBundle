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

        $builder->add(
            'percentage',
            'text',
            array(
                "label" => "Percentage",
                'required' => false
            )
        );
        $builder->add(
            'width',
            'text',
            array(
                "label" => "Width",
                'required' => false
            )
        );
        $builder->add(
            'height',
            'text',
            array(
                "label" => "Height",
                'required' => false
            )
        );
    }

    public function getName() {
        return 'KitFileFormResizeImage';
    }

}
