<?php

namespace In2code\Powermail\Tests\Unit\Utility;

use In2code\Powermail\Utility\LocalizationUtility;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class LocalizationUtilityTest
 */
#[CoversMethod(LocalizationUtility::class, 'translate')]
class LocalizationUtilityTest extends UnitTestCase
{
    #[Test]
    public function translateReturnsString(): void
    {
        $value = (string)random_int(0, mt_getrandmax());
        self::assertSame($value, LocalizationUtility::translate($value));
        self::assertSame('Y-m-d H:i', LocalizationUtility::translate('datepicker_format'));
    }
}
