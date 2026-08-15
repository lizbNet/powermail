<?php

declare(strict_types=1);
namespace In2code\Powermail\ViewHelpers\Misc;

use In2code\Powermail\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class CheckForTypoScriptViewHelper
 * @noinspection PhpUnused
 */
class CheckForTypoScriptViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('settings', 'array', 'settings array', true);
    }

    /**
     * TYPO3 v14 port: Fluid 5 no longer calls renderStatic() at all (see
     * Changelog Deprecation-104789-RenderStaticForFluidViewHelpers), so
     * this was silently dead code under v14 - converted to the instance
     * render() Fluid actually dispatches to.
     */
    public function render(): void
    {
        $argumentsSettings = $this->arguments['settings'] ?? [];
        if (($argumentsSettings['staticTemplate'] ?? 1) !== '1') {
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            /** @var FlashMessageQueue $flashMessageQueue */
            $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier('extbase.flashmessages.tx_powermail_pi1');
            /** @var FlashMessage $flashMessage */
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                LocalizationUtility::translate('error_no_typoscript'),
                '',
                ContextualFeedbackSeverity::ERROR
            );
            $flashMessageQueue->addMessage($flashMessage);
        }
    }
}
