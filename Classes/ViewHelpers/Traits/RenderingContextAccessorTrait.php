<?php

declare(strict_types=1);
namespace In2code\Powermail\ViewHelpers\Traits;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;

/**
 * Fluid 5 typed AbstractViewHelper::$renderingContext/$templateVariableContainer as
 * nullable to represent the state before setRenderingContext() runs. Both are always
 * set by the time render() executes, so these accessors only narrow the type for
 * static analysis.
 */
trait RenderingContextAccessorTrait
{
    protected function getRenderingContext(): RenderingContextInterface
    {
        assert($this->renderingContext instanceof RenderingContextInterface);
        return $this->renderingContext;
    }

    protected function getTemplateVariableContainer(): VariableProviderInterface
    {
        assert($this->templateVariableContainer instanceof VariableProviderInterface);
        return $this->templateVariableContainer;
    }
}
