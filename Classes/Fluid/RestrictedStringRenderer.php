<?php

declare(strict_types=1);

namespace In2code\Powermail\Fluid;

use In2code\Powermail\Fluid\Cache\NullFluidCache;
use In2code\Powermail\Fluid\Parser\NeutralizeModifiersTemplateProcessor;
use In2code\Powermail\Fluid\ViewHelper\RestrictedViewHelperInvoker;
use In2code\Powermail\Fluid\ViewHelper\RestrictedViewHelperResolver;
use In2code\Powermail\Fluid\ViewHelper\ViewHelperPolicy;
use In2code\Powermail\Utility\ObjectUtility;
use Throwable;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Fluid\View\FluidViewAdapter;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\NamespaceDetectionTemplateProcessor;
use TYPO3Fluid\Fluid\Core\Parser\TemplateProcessor\RemoveCommentsTemplateProcessor;

/**
 * Renders a string with Fluid so that "{marker}" placeholders are replaced, but only allowlisted
 * ViewHelpers may execute.
 *
 * Background: powermail parses several configured values with Fluid - the mail subject, the receiver
 * name and email, field titles and select options - so that editors can write "{firstname}" or
 * "{f:cObject(typoscriptObjectPath:'lib.x')}" into them. Some of those values can, depending on the
 * mail type, carry data submitted by a website visitor, which turns the whole string into a template
 * an attacker controls. Without a restriction, that allows arbitrary ViewHelper execution from an
 * unauthenticated request.
 *
 * @see ViewHelperPolicy for the allowlist
 * @see RestrictedViewHelperResolver for the parse time gate
 */
class RestrictedStringRenderer
{
    /**
     * Controller name of the rendering context, and therefore part of the identifier of the compiled
     * template. Keeps compiled templates of restricted renderings separate from the permissive ones
     * (VariablesViewHelper renders the RTE bodytext, where ViewHelpers are a documented feature).
     *
     * Both use TemplatePaths::setTemplateSource(), and the identifier is built from the source string
     * plus controller, action and format only - so without this, an identical string would share one
     * compiled class between both paths.
     */
    private const CACHE_SCOPE_CONTROLLER = 'PowermailRestrictedString';

    /**
     * @param array<string, mixed> $variables
     */
    public function render(string $string, array $variables = []): string
    {
        if (!str_contains($string, '{') && !str_contains($string, '<')) {
            return $string;
        }

        $policy = ViewHelperPolicy::fromExtensionConfiguration();
        $view = GeneralUtility::makeInstance(ViewFactoryInterface::class)->create(
            GeneralUtility::makeInstance(
                ViewFactoryData::class,
                null,
                null,
                null,
                null,
                $GLOBALS['TYPO3_REQUEST'] ?? null,
            )
        );

        if (!$view instanceof FluidViewAdapter) {
            // Another view implementation was registered, so the restriction cannot be installed.
            // Replacing markers without Fluid is the only safe thing left to do.
            return $this->replaceMarkers($string, $variables);
        }

        $renderingContext = $view->getRenderingContext();
        $resolver = new RestrictedViewHelperResolver($renderingContext->getViewHelperResolver(), $policy);
        $renderingContext->setViewHelperResolver($resolver);
        $renderingContext->setViewHelperInvoker(new RestrictedViewHelperInvoker($policy, $resolver));
        $renderingContext->setTemplateProcessors([
            GeneralUtility::makeInstance(NeutralizeModifiersTemplateProcessor::class),
            GeneralUtility::makeInstance(NamespaceDetectionTemplateProcessor::class),
            GeneralUtility::makeInstance(RemoveCommentsTemplateProcessor::class),
        ]);
        $renderingContext->setCache(GeneralUtility::makeInstance(NullFluidCache::class));
        $renderingContext->setControllerName(self::CACHE_SCOPE_CONTROLLER);
        // the fingerprint invalidates compiled templates when the allowlist changes
        $renderingContext->setControllerAction('Parse' . $policy->getFingerprint());
        $renderingContext->getTemplatePaths()->setTemplateSource($string);
        $view->assignMultiple($variables);

        try {
            return $view->render();
        } catch (Throwable $throwable) {
            ObjectUtility::getLogger(self::class)->warning(
                'powermail could not parse a string with Fluid, Fluid syntax was removed from it',
                [
                    'exception' => $throwable->getMessage(),
                    'string' => mb_substr($string, 0, 200),
                ]
            );

            return $this->removeFluidSyntax($string);
        }
    }

    /**
     * Fallback that mirrors what Fluid does to a "{marker}" - including the escaping the Escape
     * interceptor would apply - without handing the string to the parser.
     *
     * @param array<string, mixed> $variables
     */
    protected function replaceMarkers(string $string, array $variables): string
    {
        $replacements = [];
        foreach ($variables as $marker => $value) {
            if (is_array($value) || is_object($value)) {
                continue;
            }

            $replacements['{' . $marker . '}'] = htmlspecialchars((string)$value, ENT_QUOTES);
        }

        return strtr($string, $replacements);
    }

    /**
     * Used when parsing failed. Returning the string unchanged would hand the payload straight to the
     * output, and returning an empty string would silently drop mails (an empty subject makes
     * SendMailService::sendMail() return early), so every active Fluid construct is removed instead.
     */
    protected function removeFluidSyntax(string $string): string
    {
        $string = (string)preg_replace('#</?[a-zA-Z0-9]+:[^>]*>#', '', $string);
        // twice, to also catch one level of nested curly braces
        $string = (string)preg_replace('#\{[^{}]*\}#', '', $string);
        $string = (string)preg_replace('#\{[^{}]*\}#', '', $string);

        return trim($string);
    }
}
