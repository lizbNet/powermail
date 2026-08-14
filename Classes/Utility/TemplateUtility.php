<?php

declare(strict_types=1);
namespace In2code\Powermail\Utility;

use In2code\Powermail\Domain\Model\Mail;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * Class TemplateUtility
 * @codeCoverageIgnore
 */
class TemplateUtility
{
    /**
     *  Get absolute paths for templates with fallback
     *     Returns paths from *RootPaths and "hardcoded"
     *     paths pointing to the EXT:powermail-resources.
     */
    public static function getTemplateFolders(string $part = 'template'): array
    {
        $templatePaths = [];
        $extbaseConfig = ObjectUtility::getConfigurationManager()->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'powermail'
        );
        if (!empty($extbaseConfig['view'][$part . 'RootPaths'])) {
            $templatePaths = $extbaseConfig['view'][$part . 'RootPaths'];
            ksort($templatePaths, SORT_NUMERIC);
            $templatePaths = array_values($templatePaths);
        }

        if ($templatePaths === []) {
            $templatePaths[] = 'EXT:powermail/Resources/Private/' . ucfirst($part) . 's/';
        }

        $templatePaths = array_unique($templatePaths);
        $absolutePaths = [];
        foreach ($templatePaths as $templatePath) {
            $absolutePaths[] = StringUtility::addTrailingSlash(GeneralUtility::getFileAbsFileName($templatePath));
        }

        return $absolutePaths;
    }

    /**
     *  Return path and filename for a file or path.
     *  Only the first existing file/path will be returned.
     *  respect *RootPaths
     */
    public static function getTemplatePath(string $pathAndFilename, string $part = 'template'): string
    {
        $matches = self::getTemplatePaths($pathAndFilename, $part);
        return $matches === [] ? '' : end($matches);
    }

    /**
     *  Return path and filename for one or many files/paths.
     *         Only existing files/paths will be returned.
     *         respect *RootPaths
     */
    public static function getTemplatePaths(string $pathAndFilename, string $part = 'template'): array
    {
        $matches = [];
        $absolutePaths = self::getTemplateFolders($part);
        foreach ($absolutePaths as $absolutePath) {
            if (file_exists($absolutePath . $pathAndFilename)) {
                $matches[] = $absolutePath . $pathAndFilename;
            }
        }

        return $matches;
    }

    /**
     * Get a view for a known template file.
     *
     * Ported from StandaloneView (removed in TYPO3 v14, see Changelog
     * Breaking-105377/Feature-104773-GenericViewFactory) to the core
     * ViewFactoryInterface replacement. Unlike StandaloneView, the
     * replacement is immutable and needs the template path upfront
     * (there is no setTemplatePathAndFilename() on the resulting view),
     * so callers that used to build a StandaloneView first and set the
     * template path after now pass it in here directly.
     */
    public static function getView(string $templatePathAndFilename, string $format = 'html'): ViewInterface
    {
        $viewFactory = GeneralUtility::makeInstance(ViewFactoryInterface::class);
        return $viewFactory->create(
            new ViewFactoryData(
                layoutRootPaths: self::getTemplateFolders('layout'),
                partialRootPaths: self::getTemplateFolders('partial'),
                templatePathAndFilename: $templatePathAndFilename,
                request: $GLOBALS['TYPO3_REQUEST'] ?? null,
                format: $format,
            )
        );
    }

    /**
     * This functions renders the powermail_all Template (e.g. useage in Mails)
     */
    public static function powermailAll(
        Mail $mail,
        string $section = 'web',
        array $settings = [],
        ?string $type = null
    ): ?string {
        $view = self::getView(self::getTemplatePath('Form/PowermailAll.html'));
        $view->assignMultiple(
            [
                'mail' => $mail,
                'section' => $section,
                'settings' => $settings,
                'type' => $type,
            ]
        );
        return $view->render();
    }

    /**
     * Parse String with Fluid View
     *
     * ViewFactoryInterface (the StandaloneView replacement, see
     * powermailAll() above) only renders from a file path, not a raw
     * string, so this case has no direct ViewFactory equivalent. Instead
     * this drives the underlying Fluid engine directly - the same one
     * ViewFactoryInterface uses internally - via
     * TYPO3Fluid\Fluid\View\TemplatePaths::setTemplateSource(), which
     * TYPO3 core did not remove (only its own CMS-specific View wrapper
     * classes were removed). UNVERIFIED against a real TYPO3 v14
     * installation - re-check against a live instance before relying on it.
     */
    public static function fluidParseString(string $string, array $variables = []): string
    {
        if ($string === '' || $string === '0'
            || ConfigurationUtility::isDatabaseConnectionAvailable() === false
            || BackendUtility::isBackendContext()
            || Environment::isCli()
        ) {
            return $string;
        }

        $renderingContext = GeneralUtility::makeInstance(RenderingContextFactory::class)
            ->create([], $GLOBALS['TYPO3_REQUEST'] ?? null);
        $renderingContext->getTemplatePaths()->setTemplateSource($string);
        $view = new TemplateView($renderingContext);
        $view->assignMultiple($variables);
        return $view->render() ?? '';
    }
}
