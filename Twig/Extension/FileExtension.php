<?php
namespace Kitpages\FileBundle\Twig\Extension;

use Symfony\Component\Locale\Locale;

class FileExtension extends \Twig_Extension
{

    public static function htmlCollection($fieldId)
    {
        return  '<div id="kit-file-container-'.$fieldId.'" class="kit-file-container">'.
                '    <input id="kit-file-upload-'.$fieldId.'" class="file-upload-field" name="file-upload-'.$fieldId.'" type="file" />'.
                '    <ul class="kit-file-image-list">'.
                '    </ul>'.
                '</div>';
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array(
            'kit_file_htmlCollection' => new \Twig_Filter_Function('Kitpages\FileBundle\Twig\Extension\FileExtension::htmlCollection'),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'kit_file';
    }
}
