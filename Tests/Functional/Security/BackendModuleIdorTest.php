<?php

declare(strict_types=1);

namespace In2code\Powermail\Tests\Functional\Security;

use In2code\Powermail\Controller\ModuleController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Middleware\BackendModuleValidator;
use TYPO3\CMS\Backend\Module\ModuleRegistry;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Class BackendModuleIdorTest
 *
 * Proves the backend IDOR that the fix closes. A non-admin backend user, denied page-show permission on a
 * foreign page, can reach that page's powermail submissions because:
 *
 *  1. Parser differential — TYPO3's BackendModuleValidator only page-access-checks canonical integer ids
 *     (MathUtility::canBeInterpretedAsInteger()), while ModuleController::initializeAction() resolves the id
 *     with a raw (int) cast. A non-canonical id such as "050" slips past the middleware but still resolves to
 *     the foreign pid.
 *  2. Source-precedence differential — the middleware reads getQueryParams()['id'] ?? getParsedBody()['id']
 *     (query first), the controller reads getParsedBody()['id'] ?? getQueryParams()['id'] (body first). An
 *     accessible id in the query plus a foreign id in the body passes the middleware but is used by the
 *     controller.
 *  3. The MailRepository read path constrains only "deleted = 0 AND pid = <id>" — there is no backend
 *     permission filter — so the foreign submissions are returned once the id is reached.
 *
 * The middleware assertions exercise the real core code through the protected validateModuleAccess() decision
 * (isolated by reflection so the surrounding process() concerns — flash messages, module data,
 * Sec-Fetch-Dest redirects — do not affect the access-control result under test). These assertions describe
 * unchanged core behaviour and therefore stay true after the fix; the fix instead adds the missing page
 * access check in the controller (see BackendUtilityTest) and removes the legacy reader routes (asserted
 * here).
 */
final class BackendModuleIdorTest extends FunctionalTestCase
{
    private const USER_RESTRICTED = 2;
    private const PAGE_ACCESSIBLE = 20;
    private const PAGE_FOREIGN = 50;

    /**
     * A leaf powermail reader submodule, granted to the restricted editor's group, used to drive the real
     * BackendModuleValidator page-access decision.
     */
    private const MODULE_IDENTIFIER = 'powermail_list';

    protected array $testExtensionsToLoad = [
        'in2code/powermail',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_access.csv');
    }

    /**
     * Baseline: the framework DOES protect a plain, canonical foreign id — it throws the page-access
     * RuntimeException (code 1289917924). This is the protection the non-canonical encodings evade.
     */
    #[Test]
    public function coreMiddlewareBlocksCanonicalForeignId(): void
    {
        $this->setUpBackendUser(self::USER_RESTRICTED);
        $request = $this->createBackendRequest(queryId: (string)self::PAGE_FOREIGN);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1289917924);
        $this->invokeValidateModuleAccess($request);
    }

    /**
     * Sanity: the framework also lets the restricted editor reach their OWN page without error.
     */
    #[Test]
    public function coreMiddlewareAllowsAccessibleId(): void
    {
        $this->setUpBackendUser(self::USER_RESTRICTED);
        $request = $this->createBackendRequest(queryId: (string)self::PAGE_ACCESSIBLE);

        $this->invokeValidateModuleAccess($request);
        $this->addToAssertionCount(1); // no exception thrown
    }

    /**
     * The vulnerability: a non-canonical id evades the middleware's canBeInterpretedAsInteger() gate (no
     * exception is thrown) yet still resolves to the foreign pid through the controller's (int) cast.
     */
    #[Test]
    #[DataProvider('nonCanonicalForeignIdDataProvider')]
    public function coreMiddlewareDoesNotBlockNonCanonicalForeignId(string $requestId): void
    {
        $this->setUpBackendUser(self::USER_RESTRICTED);

        // The middleware skips its page-access check for these non-canonical ids ...
        self::assertFalse(MathUtility::canBeInterpretedAsInteger($requestId));
        // ... while the controller's raw (int) cast still targets the foreign page.
        self::assertSame(self::PAGE_FOREIGN, (int)$requestId);

        $request = $this->createBackendRequest(queryId: $requestId);

        // Real core decision: no exception — the foreign page slips through.
        $this->invokeValidateModuleAccess($request);
        $this->addToAssertionCount(1);
    }

    public static function nonCanonicalForeignIdDataProvider(): \Iterator
    {
        yield 'leading zero' => ['050'];
        yield 'leading plus' => ['+50'];
        yield 'trailing space' => ['50 '];
        yield 'leading space' => [' 50'];
        yield 'decimal notation' => ['50.0'];
        yield 'trailing garbage' => ['50abc'];
    }

    /**
     * The source-precedence differential: with an accessible id in the query and a foreign id in the body,
     * the middleware evaluates the accessible id (query first) and does not throw, while the controller's
     * body-first resolution targets the foreign page.
     */
    #[Test]
    public function coreMiddlewareAndControllerResolveIdFromDifferentSources(): void
    {
        $this->setUpBackendUser(self::USER_RESTRICTED);
        $request = $this->createBackendRequest(
            queryId: (string)self::PAGE_ACCESSIBLE,
            bodyId: (string)self::PAGE_FOREIGN,
        );

        // Middleware sees the accessible id (query first) -> no exception.
        $this->invokeValidateModuleAccess($request);
        $this->addToAssertionCount(1);

        // Middleware id resolution: query first.
        $middlewareId = $request->getQueryParams()['id'] ?? $request->getParsedBody()['id'] ?? 0;
        self::assertSame((string)self::PAGE_ACCESSIBLE, $middlewareId);

        // Controller id resolution (ModuleController::initializeAction): body first.
        $controllerId = (int)($request->getParsedBody()['id'] ?? $request->getQueryParams()['id'] ?? null);
        self::assertSame(self::PAGE_FOREIGN, $controllerId);
    }

    /**
     * Impact: once the foreign pid is reached, the read path returns its submissions. The MailRepository
     * constrains only "deleted = 0 AND pid = <id>" (MailRepository::getConstraintsForFindAllInPid) with no
     * backend permission filter; this asserts the same constraint set over the foreign page's seeded rows.
     */
    #[Test]
    public function foreignSubmissionsAreReturnedWithoutAnyPermissionFilter(): void
    {
        $this->setUpBackendUser(self::USER_RESTRICTED);

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_powermail_domain_model_mail');
        $queryBuilder->getRestrictions()->removeAll();
        $rows = $queryBuilder
            ->select('uid', 'sender_name', 'sender_mail')
            ->from('tx_powermail_domain_model_mail')
            ->where(
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('pid', self::PAGE_FOREIGN),
            )
            ->executeQuery()
            ->fetchAllAssociative();

        self::assertCount(2, $rows, 'The foreign page\'s submissions are readable with no permission filter.');
        $senderMails = array_column($rows, 'sender_mail');
        self::assertContains('alice@foreign.example', $senderMails);
        self::assertContains('bob@foreign.example', $senderMails);
    }

    /**
     * Defence in depth: the legacy plain backend routes that pointed straight at the submission-reading
     * actions (bypassing BackendModuleValidator's module/page-access checks) must be removed. Only the
     * HMAC-protected download route remains.
     */
    #[Test]
    public function legacyReaderRoutesAreRemoved(): void
    {
        $routes = require __DIR__ . '/../../../Configuration/Backend/Routes.php';

        foreach ([
            'powermail_list',
            'powermail_formoverview',
            'powermail_reportingform',
            'powermail_reportingmarketing',
            'powermail_functioncheck',
        ] as $removedRoute) {
            self::assertArrayNotHasKey(
                $removedRoute,
                $routes,
                sprintf('Legacy reader route "%s" must not be registered.', $removedRoute),
            );
        }

        self::assertArrayHasKey('powermail_downloadfile', $routes);
        self::assertSame(
            ModuleController::class . '::downloadFile',
            $routes['powermail_downloadfile']['target'],
        );
    }

    /**
     * Build a backend ServerRequest carrying the resolved module route and an "id" in the requested sources,
     * matching what BackendModuleValidator and ModuleController::initializeAction() read.
     */
    private function createBackendRequest(?string $queryId = null, ?string $bodyId = null): ServerRequestInterface
    {
        $module = $this->get(ModuleRegistry::class)->getModule(self::MODULE_IDENTIFIER);

        $request = (new ServerRequest('https://typo3-testing.local/typo3/', 'GET'))
            ->withAttribute('module', $module);

        if ($queryId !== null) {
            $request = $request->withQueryParams(['id' => $queryId]);
        }
        if ($bodyId !== null) {
            $request = $request->withParsedBody(['id' => $bodyId]);
        }

        return $request;
    }

    /**
     * Invoke the real, protected BackendModuleValidator::validateModuleAccess() decision in isolation.
     */
    private function invokeValidateModuleAccess(ServerRequestInterface $request): void
    {
        $validator = $this->get(BackendModuleValidator::class);
        $module = $request->getAttribute('module');

        $method = new \ReflectionMethod($validator, 'validateModuleAccess');
        $method->invoke($validator, $request, $module);
    }
}
