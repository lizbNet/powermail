<?php

namespace In2code\Powermail\Tests\Unit\Utility;

use In2code\Powermail\Utility\TypoScriptUtility;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class TypoScriptUtilityTest
 */
#[CoversMethod(TypoScriptUtility::class, 'getCaptchaExtensionFromSettings')]
class TypoScriptUtilityTest extends UnitTestCase
{
    #[Test]
    public function getCaptchaExtensionFromSettingsReturnsString(): void
    {
        $settings = [
            'captcha' => [
                'use' => [
                    'captcha',
                ],
            ],
        ];
        $value = TypoScriptUtility::getCaptchaExtensionFromSettings($settings);
        self::assertSame('default', $value);
    }
}
