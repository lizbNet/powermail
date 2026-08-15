<?php

namespace In2code\Powermail\Tests\Unit\ViewHelpers\Misc;

use In2code\Powermail\Domain\Model\Field;
use In2code\Powermail\ViewHelpers\Misc\PrefillFieldViewHelper;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class PrefillFieldViewHelperTest
 */
#[CoversMethod(\In2code\Powermail\ViewHelpers\Misc\PrefillFieldViewHelper::class, 'render')]
#[CoversMethod(\In2code\Powermail\ViewHelpers\Misc\PrefillFieldViewHelper::class, 'getValue')]
#[CoversMethod(\In2code\Powermail\ViewHelpers\Misc\PrefillFieldViewHelper::class, 'buildValue')]
#[CoversMethod(\In2code\Powermail\ViewHelpers\Misc\PrefillFieldViewHelper::class, 'getFromTypoScriptRaw')]
class PrefillFieldViewHelperTest extends UnitTestCase
{
    protected MockObject $abstractValidationViewHelperMock;

    public function setUp(): void
    {
        $listenerProviderMock = $this->getMockBuilder(ListenerProviderInterface::class)->getMock();
        $eventDispatcher = new EventDispatcher($listenerProviderMock);
        $this->abstractValidationViewHelperMock = $this->getAccessibleMock(
            PrefillFieldViewHelper::class,
            null,
            [$eventDispatcher]
        );
    }

    public function tearDown(): void
    {
        unset($this->generalValidatorMock);
    }

    public static function getDefaultValueReturnsStringDataProvider(): array
    {
        return [
            [
                [ // field values
                    'uid' => 123,
                    'marker' => 'marker',
                    'prefillValue' => 'mno',
                ],
                [ // variables from POST
                    'field' => [
                        'marker' => 'abc',
                        '123' => 'ghi',
                    ],
                    'marker' => 'def',
                    'uid123' => 'jkl',
                ],
                [ // configuration
                    'prefill.' => [
                        'marker' => 'pqr',
                    ],
                ],
                'abc', // expected
            ],
            [
                [
                    'uid' => 123,
                    'marker' => 'marker',
                    'prefillValue' => 'mno',
                ],
                [
                    'field' => [
                        '123' => 'ghi',
                    ],
                    'marker' => 'def',
                    'uid123' => 'jkl',
                ],
                [
                    'prefill.' => [
                        'marker' => 'pqr',
                    ],
                ],
                'def',
            ],
            [
                [
                    'uid' => 123,
                    'marker' => 'marker',
                    'prefillValue' => 'mno',
                ],
                [
                    'field' => [
                        '123' => 'ghi',
                    ],
                    'uid123' => 'jkl',
                ],
                [
                    'prefill.' => [
                        'marker' => 'pqr',
                    ],
                ],
                'mno',
            ],
            [
                [
                    'uid' => 123,
                    'marker' => 'marker',
                    'prefillValue' => 'mno',
                ],
                [
                    'uid123' => 'jkl',
                ],
                [
                    'prefill.' => [
                        'marker' => 'pqr',
                    ],
                ],
                'mno',
            ],
            [
                [
                    'uid' => 123,
                    'marker' => 'marker',
                    'prefillValue' => 'mno',
                ],
                [],
                [
                    'prefill.' => [
                        'marker' => 'pqr',
                    ],
                ],
                'mno',
            ],
            [
                [
                    'uid' => 123,
                    'marker' => 'marker',
                    'prefillValue' => 'mno',
                ],
                [],
                [],
                'mno',
            ],
            [
                [
                    'uid' => 123,
                    'marker' => 'marker',
                ],
                [],
                [
                    'prefill.' => [
                        'marker' => 'pqr',
                    ],
                ],
                'pqr',
            ],
            [
                [
                    'uid' => 123,
                    'marker' => 'marker',
                    'prefillValue' => 'mno',
                ],
                [
                    'field' => [
                        'marker' => '',
                        '123' => 'ghi',
                    ],
                    'marker' => 'def',
                    'uid123' => 'jkl',
                ],
                [
                    'prefill.' => [
                        'marker' => 'pqr',
                    ],
                ],
                'def',
            ],
            [
                [
                    'uid' => 123,
                    'marker' => 'marker',
                ],
                [],
                [],
                '',
            ],
        ];
    }

    /**
     * @param array $fieldValues
     * @param array $variables
     * @param array $configuration
     * @param string $expectedResult
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     */
    #[Test]
    #[DataProvider('getDefaultValueReturnsStringDataProvider')]
    public function getDefaultValueReturnsString($fieldValues, $variables, $configuration, $expectedResult): void
    {
        $field = new Field();
        foreach ($fieldValues as $name => $value) {
            $field->_setProperty($name, $value);
        }

        $this->abstractValidationViewHelperMock->_set('contentObject', $this->createMock(ContentObjectRenderer::class));
        $this->abstractValidationViewHelperMock->_set('variables', $variables);
        $this->abstractValidationViewHelperMock->_set('configuration', $configuration);
        $this->abstractValidationViewHelperMock->_set('field', $field);
        $this->abstractValidationViewHelperMock->_set('marker', $field->getMarker());
        $this->abstractValidationViewHelperMock->_call('buildValue');
        self::assertSame($expectedResult, $this->abstractValidationViewHelperMock->_call('getValue'));
    }

    public static function getFromTypoScriptRawReturnsStringDataProvider(): array
    {
        return [
            [
                [
                    'prefill.' => [
                        'email' => 'abcdef',
                    ],
                ],
                'email',
                'abcdef',
            ],
            [
                [
                    'prefill.' => [
                        'email' => 'TEXT',
                        'email.' => [
                            'value' => 'xyz',
                        ],
                    ],
                ],
                'email',
                '',
            ],
            [
                [
                    'prefill.' => [
                        'marker' => 'TEXT',
                    ],
                ],
                'marker',
                'TEXT',
            ],
        ];
    }

    /**
     * @param string $marker
     * @param string $expectedResult
     */
    #[Test]
    #[DataProvider('getFromTypoScriptRawReturnsStringDataProvider')]
    public function getFromTypoScriptRawReturnsString(array $configuration, $marker, $expectedResult): void
    {
        $this->abstractValidationViewHelperMock->_set('configuration', $configuration);
        $this->abstractValidationViewHelperMock->_set('marker', $marker);

        $value = '';
        self::assertSame(
            $expectedResult,
            $this->abstractValidationViewHelperMock->_call('getFromTypoScriptRaw', $value)
        );
    }
}
