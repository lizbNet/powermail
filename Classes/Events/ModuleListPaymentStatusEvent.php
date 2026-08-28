<?php

declare(strict_types=1);
namespace In2code\Powermail\Events;

/**
 * Dispatched from ModuleController::listAction() so an extension can annotate the backend
 * submission list with per-mail data it owns, without powermail depending on that extension
 * directly. No listener is registered here -- if none is registered elsewhere, paymentStatuses
 * simply stays empty and the list renders with no extra column.
 *
 * Carries the paginated mail objects, not just their uids: a listener needs each mail's own
 * data (sender email, creation date, answers) to do anything useful, and mail objects are
 * already hydrated by the time this fires -- uids alone would force a listener to re-query
 * data listAction() already loaded.
 */
final class ModuleListPaymentStatusEvent
{
    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $paymentStatuses = [];

    /**
     * @param  iterable<\In2code\Powermail\Domain\Model\Mail>  $mails  the current page's paginated mails
     */
    public function __construct(protected iterable $mails, protected int $pageUid)
    {
    }

    /**
     * @return iterable<\In2code\Powermail\Domain\Model\Mail>
     */
    public function getMails(): iterable
    {
        return $this->mails;
    }

    public function getPageUid(): int
    {
        return $this->pageUid;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPaymentStatuses(): array
    {
        return $this->paymentStatuses;
    }

    /**
     * @param  array<int, array<string, mixed>>  $paymentStatuses  keyed by mail uid
     */
    public function setPaymentStatuses(array $paymentStatuses): ModuleListPaymentStatusEvent
    {
        $this->paymentStatuses = $paymentStatuses;
        return $this;
    }
}
