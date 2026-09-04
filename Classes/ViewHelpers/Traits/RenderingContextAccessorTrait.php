<?php

declare(strict_types=1);
namespace In2code\Powermail\ViewHelpers\Traits;

use Psr\Http\Message\ServerRequestInterface;
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

    /**
     * Shortcut for retrieving the PSR-7 request from the rendering context.
     *
     * TYPO3 v14 port: RenderingContext::getRequest()/setRequest() were
     * removed (see Changelog Deprecation-104684-FluidRenderingContext-
     * getRequest); the request is now exposed as a rendering context
     * attribute instead.
     *
     * Deliberately NOT named getRequest(): TYPO3\CMS\Fluid\ViewHelpers\
     * Form\AbstractFormFieldViewHelper already declares its own
     * getRequest(): RequestInterface (Extbase's request type, not PSR-7) -
     * a class using this trait while also extending that base class would
     * otherwise fatal with an incompatible method declaration.
     */
    protected function getServerRequest(): ?ServerRequestInterface
    {
        $renderingContext = $this->getRenderingContext();
        if ($renderingContext->hasAttribute(ServerRequestInterface::class)) {
            return $renderingContext->getAttribute(ServerRequestInterface::class);
        }

        return null;
    }
}
