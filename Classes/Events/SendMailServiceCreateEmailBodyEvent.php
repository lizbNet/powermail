<?php

declare(strict_types=1);
namespace In2code\Powermail\Events;

use In2code\Powermail\Domain\Service\Mail\SendMailService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\View\ViewInterface;

/**
 * TYPO3 v14 port: StandaloneView was removed from core (see Changelog
 * Breaking-105377/Feature-104773-GenericViewFactory), so the property/getter/
 * setter here changed from StandaloneView to the replacement ViewInterface.
 * This is a breaking change for anyone listening to this PSR-14 event.
 */
final class SendMailServiceCreateEmailBodyEvent
{
    public function __construct(
        protected ViewInterface $view,
        protected array $email,
        protected SendMailService $sendMailService,
        private ?ServerRequestInterface $request = null
    ) {
    }

    public function getView(): ViewInterface
    {
        return $this->view;
    }

    public function setView(ViewInterface $view): SendMailServiceCreateEmailBodyEvent
    {
        $this->view = $view;
        return $this;
    }

    public function getEmail(): array
    {
        return $this->email;
    }

    public function setEmail(array $email): SendMailServiceCreateEmailBodyEvent
    {
        $this->email = $email;
        return $this;
    }

    public function getSendMailService(): SendMailService
    {
        return $this->sendMailService;
    }

    /**
     * @return ServerRequestInterface|null
     */
    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }
}
