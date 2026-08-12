<?php

declare(strict_types=1);

namespace In2code\Powermail\Tests\Unit\Fluid\ViewHelper;

use In2code\Powermail\Fluid\ViewHelper\BlockedViewHelper;
use In2code\Powermail\Fluid\ViewHelper\RestrictedViewHelperResolver;
use In2code\Powermail\Fluid\ViewHelper\ViewHelperPolicy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\ViewHelpers\Format\TrimViewHelper;

/**
 * Class RestrictedViewHelperResolverTest
 */
#[CoversClass(RestrictedViewHelperResolver::class)]
#[CoversMethod(RestrictedViewHelperResolver::class, 'addNamespace')]
#[CoversMethod(RestrictedViewHelperResolver::class, 'addLocalNamespace')]
#[CoversMethod(RestrictedViewHelperResolver::class, 'setLocalNamespaces')]
#[CoversMethod(RestrictedViewHelperResolver::class, 'getLocalNamespaces')]
#[CoversMethod(RestrictedViewHelperResolver::class, 'isNamespaceValid')]
#[CoversMethod(RestrictedViewHelperResolver::class, 'isNamespaceIgnored')]
#[CoversMethod(RestrictedViewHelperResolver::class, 'resolveViewHelperClassName')]
#[CoversMethod(RestrictedViewHelperResolver::class, 'getResponsibleDelegate')]
#[CoversMethod(RestrictedViewHelperResolver::class, 'createViewHelperInstanceFromClassName')]
final class RestrictedViewHelperResolverTest extends UnitTestCase
{
    /**
     * blocked ViewHelpers are logged, which instantiates a LogManager singleton
     */
    protected bool $resetSingletonInstances = true;

    /**
     * @param string[] $allowedViewHelpers
     */
    private function getSubject(array $allowedViewHelpers): RestrictedViewHelperResolver
    {
        $decorated = new ViewHelperResolver();
        $decorated->setNamespaces(['f' => ['TYPO3Fluid\\Fluid\\ViewHelpers']]);

        return new RestrictedViewHelperResolver($decorated, ViewHelperPolicy::fromIdentifiers($allowedViewHelpers));
    }

    #[Test]
    public function namespaceOfAnAllowedViewHelperIsValid(): void
    {
        $subject = $this->getSubject(['f:format.trim']);

        self::assertTrue($subject->isNamespaceValid('f'));
        self::assertFalse($subject->isNamespaceIgnored('f'));
    }

    #[Test]
    public function unknownNamespaceIsIgnoredInsteadOfInvalid(): void
    {
        $subject = $this->getSubject(['f:format.trim']);

        self::assertFalse($subject->isNamespaceValid('i'));
        self::assertTrue($subject->isNamespaceIgnored('i'));
    }

    /**
     * With an empty allowlist not a single namespace is relevant, so every tag stays literal text
     */
    #[Test]
    public function everyNamespaceIsIgnoredWithAnEmptyAllowlist(): void
    {
        $subject = $this->getSubject([]);

        self::assertTrue($subject->isNamespaceIgnored('f'));
        self::assertTrue($subject->isNamespaceIgnored('i'));
    }

    /**
     * "{namespace x=...}" and 'xmlns:x="..."' are applied through addLocalNamespace()
     */
    #[Test]
    public function addLocalNamespaceDoesNotRegisterAnything(): void
    {
        $subject = $this->getSubject(['f:format.trim']);
        $subject->addLocalNamespace('i', 'TYPO3\\CMS\\Install\\ViewHelpers');

        self::assertSame([], $subject->getLocalNamespaces());
        self::assertTrue($subject->isNamespaceIgnored('i'));
    }

    #[Test]
    public function setLocalNamespacesDoesNotRegisterAnything(): void
    {
        $subject = $this->getSubject(['f:format.trim']);
        $subject->setLocalNamespaces(['i' => ['TYPO3\\CMS\\Install\\ViewHelpers']]);

        self::assertSame([], $subject->getLocalNamespaces());
        self::assertTrue($subject->isNamespaceIgnored('i'));
    }

    #[Test]
    public function addNamespaceDoesNotRegisterAnything(): void
    {
        $subject = $this->getSubject(['f:format.trim']);
        $subject->addNamespace('i', 'TYPO3\\CMS\\Install\\ViewHelpers');

        self::assertArrayNotHasKey('i', $subject->getNamespaces());
        self::assertTrue($subject->isNamespaceIgnored('i'));
    }

    #[Test]
    public function allowedViewHelperResolvesToItsRealClass(): void
    {
        self::assertSame(
            TrimViewHelper::class,
            $this->getSubject(['f:format.trim'])->resolveViewHelperClassName('f', 'format.trim')
        );
    }

    #[Test]
    public function notAllowedViewHelperResolvesToTheBlockedViewHelper(): void
    {
        self::assertSame(
            BlockedViewHelper::class,
            $this->getSubject(['f:format.trim'])->resolveViewHelperClassName('f', 'format.raw')
        );
    }

    /**
     * An allowed name that does not exist must not abort the rendering of the whole value
     */
    #[Test]
    public function allowedButUnresolvableViewHelperResolvesToTheBlockedViewHelper(): void
    {
        self::assertSame(
            BlockedViewHelper::class,
            $this->getSubject(['f:doesNotExist'])->resolveViewHelperClassName('f', 'doesNotExist')
        );
    }

    #[Test]
    public function notAllowedViewHelperHasNoResponsibleDelegate(): void
    {
        self::assertNull($this->getSubject(['f:format.trim'])->getResponsibleDelegate('f', 'format.raw'));
    }

    #[Test]
    public function blockedViewHelperIsInstantiatedWithoutTheDecoratedResolver(): void
    {
        self::assertInstanceOf(
            BlockedViewHelper::class,
            $this->getSubject([])->createViewHelperInstanceFromClassName(BlockedViewHelper::class)
        );
    }

    #[Test]
    public function blockedViewHelperRendersNothing(): void
    {
        self::assertSame('', (new BlockedViewHelper())->render());
    }
}
