<?php

namespace In2code\Powermail\Tests\Unit\ViewHelpers\Validation;

use In2code\Powermail\ViewHelpers\Validation\AbstractValidationViewHelper;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class AbstractValidationViewHelperTest
 */
#[CoversMethod(\In2code\Powermail\ViewHelpers\Validation\AbstractValidationViewHelper::class, 'isNativeValidationEnabled')]
#[CoversMethod(\In2code\Powermail\ViewHelpers\Validation\AbstractValidationViewHelper::class, 'isClientValidationEnabled')]
class AbstractValidationViewHelperTest extends UnitTestCase
{
    /**
     * @var \TYPO3\CMS\Core\Tests\AccessibleObjectInterface
     */
    protected $abstractValidationViewHelperMock;

    public function setUp(): void
    {
        $this->abstractValidationViewHelperMock = $this->getAccessibleMock(
            AbstractValidationViewHelper::class,
            ['render']
        );
    }

    public function tearDown(): void
    {
        unset($this->generalValidatorMock);
    }

    /**
     * Dataprovider for
     *        isNativeValidationEnabledReturnsBool()
     *        isClientValidationEnabledReturnsBool()
     */
    public static function isValidationEnabledReturnsBoolDataProvider(): array
    {
        return [
            'nativeAndClientActivated' => [
                [
                    'validation' => [
                        'native' => '1',
                        'client' => '1',
                    ],
                ],
                true,
                true,
            ],
            'nativeOnlyActivated' => [
                [
                    'validation' => [
                        'native' => '1',
                        'client' => '0',
                    ],
                ],
                true,
                false,
            ],
            'clientOnlyActivated' => [
                [
                    'validation' => [
                        'native' => '0',
                        'client' => '1',
                    ],
                ],
                false,
                true,
            ],
            'nothingActivated' => [
                [
                    'validation' => [
                        'native' => '0',
                        'client' => '0',
                    ],
                ],
                false,
                false,
            ],
        ];
    }

    /**
     * @param array $settings
     * @param bool $expectedNativeResult
     * @param bool $expectedClientResult
     */
    #[Test]
    #[DataProvider('isValidationEnabledReturnsBoolDataProvider')]
    public function isNativeValidationEnabledReturnsBool($settings, $expectedNativeResult, $expectedClientResult): void
    {
        unset($expectedClientResult);
        $this->abstractValidationViewHelperMock->_set('settings', $settings);
        $result = $this->abstractValidationViewHelperMock->_call('isNativeValidationEnabled');
        self::assertSame($expectedNativeResult, $result);
    }

    /**
     * @param array $settings
     * @param bool $expectedNativeResult
     * @param bool $expectedClientResult
     */
    #[Test]
    #[DataProvider('isValidationEnabledReturnsBoolDataProvider')]
    public function isClientValidationEnabledReturnsBool($settings, $expectedNativeResult, $expectedClientResult): void
    {
        unset($expectedNativeResult);
        $this->abstractValidationViewHelperMock->_set('settings', $settings);
        $result = $this->abstractValidationViewHelperMock->_call('isClientValidationEnabled');
        self::assertSame($expectedClientResult, $result);
    }
}
