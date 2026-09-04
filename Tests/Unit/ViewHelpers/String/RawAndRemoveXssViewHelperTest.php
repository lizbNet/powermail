<?php

declare(strict_types=1);
namespace In2code\Powermail\Tests\Unit\ViewHelpers\String;

use In2code\Powermail\ViewHelpers\String\EscapeLabelsViewHelper;
use In2code\Powermail\ViewHelpers\String\RawAndRemoveXssViewHelper;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class RawAndRemoveXssViewHelperTest
 *
 * Legacy per-project field partials (from before this class was renamed to
 * EscapeLabelsViewHelper) still reference <vh:string.RawAndRemoveXss>. If
 * this class is ever removed again, Fluid fails to resolve it on every
 * render of the affected forms -- and since FormController::processRequest()
 * forwards back to 'form' on any exception, that failure loops silently
 * instead of surfacing, until Extbase's dispatch loop gives up.
 */
#[CoversMethod(RawAndRemoveXssViewHelper::class, 'render')]
class RawAndRemoveXssViewHelperTest extends UnitTestCase
{
    #[Test]
    public function isAnEscapeLabelsViewHelper(): void
    {
        self::assertInstanceOf(EscapeLabelsViewHelper::class, new RawAndRemoveXssViewHelper());
    }
}
