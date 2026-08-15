<?php

declare(strict_types=1);

namespace In2code\Powermail\ViewHelpers\Validation;

use Doctrine\DBAL\Exception as DBALException;
use In2code\Powermail\Domain\Model\Field;
use In2code\Powermail\ViewHelpers\Traits\RenderingContextAccessorTrait;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns Error Class if Error in form
 */
class ErrorClassViewHelper extends AbstractViewHelper
{
    use RenderingContextAccessorTrait;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('field', Field::class, 'Field', true);
        $this->registerArgument('class', 'string', 'Class name', false, 'error');
    }

    /**
     * @throws DBALException
     */
    public function render(): string
    {
        /** @var Field $field */
        $field = $this->arguments['field'];
        $request = $this->getServerRequest();
        if (!$request instanceof ServerRequestInterface) {
            return '';
        }
        $validationResults = $request->getAttribute('extbase')->getOriginalRequestMappingResults();
        $errors = $validationResults->getFlattenedErrors();
        foreach ($errors as $error) {
            /** @var Error $singleError */
            foreach ((array)$error as $singleError) {
                if (!empty($singleError->getArguments()['marker'])
                    && $field->getMarker() === $singleError->getArguments()['marker']) {
                    return $this->arguments['class'];
                }
            }
        }

        return '';
    }
}
