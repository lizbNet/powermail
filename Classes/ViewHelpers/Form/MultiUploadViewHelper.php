<?php

declare(strict_types=1);

namespace In2code\Powermail\ViewHelpers\Form;

use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;

/**
 * Class MultiUploadViewHelper
 *
 * ToDo: Test, whether this class can be replaced by \TYPO3\CMS\Fluid\ViewHelpers\Form\UploadViewHelper
 */
class MultiUploadViewHelper extends AbstractFormFieldViewHelper
{
    protected $tagName = 'input';

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
        $this->registerArgument('multiple', 'string', 'Specifies that the file input element should allow multiple selection of files');
        $this->registerArgument('accept', 'string', 'Specifies the allowed file extensions to upload via comma-separated list, example ".png,.gif"');
        $this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this ViewHelper', false, 'f3-form-error');
    }

    /**
     * Renders the upload field.
     */
    public function render(): string
    {
        $name = $this->getName();
        $allowedFields = ['name', 'type', 'tmp_name', 'error', 'size'];
        foreach ($allowedFields as $fieldName) {
            $this->registerFieldNameForFormTokenGeneration($name . '[' . $fieldName . '][]');
        }

        $this->tag->addAttribute('type', 'file');
        if (!empty($this->arguments['disabled'])) {
            $this->tag->addAttribute('disabled', $this->arguments['disabled']);
        }
        if (!empty($this->arguments['multiple'])) {
            $this->tag->addAttribute('multiple', $this->arguments['multiple']);
        }
        if (!empty($this->arguments['accept'])) {
            $this->tag->addAttribute('accept', $this->arguments['accept']);
        }
        $name .= '[]';
        $this->tag->addAttribute('name', $name);
        $this->setErrorClassAttribute();
        return $this->tag->render();
    }
}
