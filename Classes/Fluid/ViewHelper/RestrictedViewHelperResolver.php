<?php

declare(strict_types=1);

namespace In2code\Powermail\Fluid\ViewHelper;

use In2code\Powermail\Utility\ObjectUtility;
use Throwable;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolverDelegateInterface;

/**
 * ViewHelperResolver for strings that powermail parses with Fluid (mail subject, receiver name,
 * field title, ...). It allows variable interpolation but only lets allowlisted ViewHelpers resolve.
 *
 * Why the resolver and not the ViewHelperInvoker: ViewHelperNode::__construct() resolves the class
 * before a ViewHelper can contribute compiled code, so a ViewHelper that does not resolve never gets
 * a node. Blocking in the invoker would miss every ViewHelper that overrides compile()/convert() and
 * emits inline PHP - f:format.raw, f:then, f:else, f:switch, f:section, f:layout, f:cache.* and more.
 *
 * All resolution and instantiation is delegated to the resolver that TYPO3 built for this rendering
 * context, so allowlisted ViewHelpers keep being instantiated through the DI container and the
 * globally registered namespaces and resolver delegates keep working.
 */
final class RestrictedViewHelperResolver extends ViewHelperResolver
{
    public function __construct(
        private readonly ViewHelperResolver $decorated,
        private readonly ViewHelperPolicy $policy,
    ) {
    }

    /**
     * Namespace imports from within the parsed string are ignored. Fluid registers
     * "{namespace x=...}" and 'xmlns:x="..."' through addLocalNamespace(), so a no-op here is what
     * actually neutralises them. Without this, an allowlisted identifier could be re-aliased -
     * "{namespace f=Vendor\Evil\ViewHelpers}<f:if ...>" would keep an allowed name while pointing it
     * at a class of the attacker's choice.
     */
    public function addLocalNamespace(string $identifier, ?string $phpNamespace): void
    {
    }

    /**
     * @param array<string, (string|null)[]|null> $namespaces
     */
    public function setLocalNamespaces(array $namespaces): void
    {
    }

    /**
     * @return array<string, (string|null)[]>
     */
    public function getLocalNamespaces(): array
    {
        return [];
    }

    /**
     * @param string|string[]|ViewHelperResolverDelegateInterface|null $phpNamespace
     */
    public function addNamespace(string $identifier, string|array|ViewHelperResolverDelegateInterface|null $phpNamespace): void
    {
    }

    /**
     * @param array<string, string|ViewHelperResolverDelegateInterface|(string|ViewHelperResolverDelegateInterface)[]|null> $namespaces
     */
    public function setNamespaces(array $namespaces): void
    {
    }

    /**
     * @return array<string, (string|null)[]|null>
     */
    public function getNamespaces(): array
    {
        return $this->decorated->getNamespaces();
    }

    /**
     * A namespace is only valid if the allowlist holds at least one entry for it. Everything else is
     * reported as ignored, which makes the parser keep the tag as literal text instead of raising an
     * UnknownNamespaceException.
     */
    public function isNamespaceValid(string $namespaceIdentifier): bool
    {
        return $this->decorated->isNamespaceValid($namespaceIdentifier)
            && !$this->decorated->isNamespaceIgnored($namespaceIdentifier)
            && $this->policy->isNamespaceRelevant($namespaceIdentifier);
    }

    public function isNamespaceIgnored(string $namespaceIdentifier): bool
    {
        return !$this->isNamespaceValid($namespaceIdentifier);
    }

    public function resolveViewHelperClassName(string $namespaceIdentifier, string $methodIdentifier): string
    {
        if ($this->policy->isAllowed($namespaceIdentifier, $methodIdentifier)) {
            try {
                return $this->decorated->resolveViewHelperClassName($namespaceIdentifier, $methodIdentifier);
            } catch (Throwable $throwable) {
                // An allowlisted name that does not resolve to a class must not abort the rendering of
                // the whole value - that would replace a subject or a field title with its defanged
                // fallback. This also catches the attempt to point an allowlisted identifier at another
                // PHP namespace, because the import itself is already ignored by addLocalNamespace().
                ObjectUtility::getLogger(self::class)->warning(
                    'powermail could not resolve an allowed ViewHelper in a string that is parsed with Fluid',
                    [
                        'viewHelper' => $namespaceIdentifier . ':' . $methodIdentifier,
                        'exception' => $throwable->getMessage(),
                    ]
                );

                return BlockedViewHelper::class;
            }
        }

        ObjectUtility::getLogger(self::class)->warning(
            'powermail blocked a ViewHelper in a string that is parsed with Fluid',
            ['viewHelper' => $namespaceIdentifier . ':' . $methodIdentifier]
        );

        return BlockedViewHelper::class;
    }

    public function getResponsibleDelegate(
        string $namespaceIdentifier,
        string $methodIdentifier
    ): ?ViewHelperResolverDelegateInterface {
        if (!$this->policy->isAllowed($namespaceIdentifier, $methodIdentifier)) {
            return null;
        }

        return $this->decorated->getResponsibleDelegate($namespaceIdentifier, $methodIdentifier);
    }

    /**
     * Instantiating is left to the decorated resolver, so allowlisted ViewHelpers keep coming from the
     * DI container. BlockedViewHelper has no constructor arguments and is created by the same path.
     *
     * @template T of ViewHelperInterface
     * @param class-string<T> $viewHelperClassName
     * @return T
     */
    public function createViewHelperInstanceFromClassName(string $viewHelperClassName): ViewHelperInterface
    {
        return $this->decorated->createViewHelperInstanceFromClassName($viewHelperClassName);
    }

    public function getResolverDelegate(string $delegateClassName): ViewHelperResolverDelegateInterface
    {
        return $this->decorated->getResolverDelegate($delegateClassName);
    }

    public function createResolverDelegateInstanceFromClassName(
        string $delegateClassName
    ): ViewHelperResolverDelegateInterface {
        return $this->decorated->createResolverDelegateInstanceFromClassName($delegateClassName);
    }

    public function getScopedCopy(): ViewHelperResolver
    {
        return clone $this;
    }
}
