<?php

namespace In2code\Powermail\Tests\Unit\Utility;

use In2code\Powermail\Utility\AbstractUtility;
use In2code\Powermail\Utility\ArrayUtility;
use In2code\Powermail\Utility\ConfigurationUtility;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class ConfigurationUtilityTest
 */
#[CoversMethod(ConfigurationUtility::class, 'getDefaultMailFromAddress')]
#[CoversMethod(AbstractUtility::class, 'getTypo3ConfigurationVariables')]
#[CoversMethod(ConfigurationUtility::class, 'getDefaultMailFromName')]
#[CoversMethod(ConfigurationUtility::class, 'getIconPath')]
#[CoversMethod(ConfigurationUtility::class, 'isValidationEnabled')]
#[CoversMethod(ConfigurationUtility::class, 'mergeTypoScript2FlexForm')]
#[CoversMethod(ArrayUtility::class, 'arrayMergeRecursiveOverrule')]
class ConfigurationUtilityTest extends UnitTestCase
{
    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    #[Test]
    public function getDefaultMailFromAddressReturnsString(): void
    {
        $testString1 = 'test@mail.org';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        self::assertSame($testString1, ConfigurationUtility::getDefaultMailFromAddress($testString1));

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        self::assertEmpty(ConfigurationUtility::getDefaultMailFromAddress());

        $testString2 = 'test@mail.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $testString2;
        self::assertSame($testString2, ConfigurationUtility::getDefaultMailFromAddress());
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    #[Test]
    public function getDefaultMailFromNameReturnsString(): void
    {
        $testString = 'randomName';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $testString;
        self::assertSame($testString, ConfigurationUtility::getDefaultMailFromName());

        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';
        self::assertEmpty(ConfigurationUtility::getDefaultMailFromName());
    }

    #[Test]
    public function getIconPathReturnsString(): void
    {
        $icon = 'random';
        self::assertSame('EXT:powermail/Resources/Public/Icons/' . $icon, ConfigurationUtility::getIconPath($icon));
    }

    #[Test]
    public function isValidationEnabledReturnsBool(): void
    {
        $settings = [
            'spamshield' => [
                '_enable' => '1',
                'methods' => [
                    [
                        'class' => 'anyClass',
                        '_enable' => '1',
                    ],
                ],
            ],
        ];
        self::assertTrue(ConfigurationUtility::isvalidationenabled($settings, 'anyClass'));
    }

    public static function mergeTypoScript2FlexFormReturnsVoidDataProvider(): array
    {
        return [
            'empty' => [
                [],
                '',
                [],
            ],
            'simple' => [
                [
                    'setup' => [
                        'abc' => 'def',
                    ],
                    'flexform' => [
                        'ghi' => 'jkl',
                    ],
                ],
                'setup',
                [
                    'abc' => 'def',
                    'ghi' => 'jkl',
                ],
            ],
            'override settings with flexform - level 1' => [
                [
                    'setup' => [
                        'main' => [
                            'pid' => '124',
                        ],
                    ],
                    'flexform' => [
                        'main' => [
                            'pid' => '123',
                        ],
                    ],
                ],
                'setup',
                [
                    'main' => [
                        'pid' => '123',
                    ],
                ],
            ],
            'override flexform only if not empty' => [
                [
                    'setup' => [
                        'prop1' => 'val1',
                        'prop2' => 'val2',
                    ],
                    'flexform' => [
                        'prop1' => '',
                        'prop2' => 'val3',
                    ],
                ],
                'setup',
                [
                    'prop1' => 'val1',
                    'prop2' => 'val3',
                ],
            ],
            'override flexform only if not empty - level 2' => [
                [
                    'setup' => [
                        'prop1' => [
                            'prop11' => 'val1',
                            'prop12' => 'val2',
                        ],
                    ],
                    'flexform' => [
                        'prop1' => [
                            'prop11' => '',
                            'prop12' => 'val3',
                        ],
                    ],
                ],
                'setup',
                [
                    'prop1' => [
                        'prop11' => 'val1',
                        'prop12' => 'val3',
                    ],
                ],
            ],
            'complex' => [
                [
                    'setup' => [
                        'receiver' => [
                            'mailformat' => 'html',
                            'default' => [
                                'senderName' => 'TEXT',
                                'senderName.' => [
                                    'value' => 'abc',
                                ],
                            ],
                        ],
                        'captcha' => [
                            'default' => [
                                'image' => 'abc.jpg',
                            ],
                        ],
                    ],
                    'flexform' => [
                        'receiver' => [
                            'mailformat' => '',
                        ],
                        'captcha' => [
                            'default' => [
                                'image' => 'def.jpg',
                            ],
                        ],
                    ],
                ],
                'setup',
                [
                    'receiver' => [
                        'mailformat' => 'html',
                        'default' => [
                            'senderName' => 'TEXT',
                            'senderName.' => [
                                'value' => 'abc',
                            ],
                        ],
                    ],
                    'captcha' => [
                        'default' => [
                            'image' => 'def.jpg',
                        ],
                    ],
                ],
            ],
            'Pi2' => [
                [
                    'setup' => [
                        'prop' => 'props',
                    ],
                    'Pi2' => [
                        'prop' => 'propp',
                    ],
                ],
                'Pi2',
                [
                    'setup' => [
                        'prop' => 'props',
                    ],
                    'Pi2' => [
                        'prop' => 'propp',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $settings
     * @param string $level
     * @param array $expectedResult
     */
    #[DataProvider('mergeTypoScript2FlexFormReturnsVoidDataProvider')]
    public function testMergeTypoScript2FlexFormReturnsVoid($settings, $level, $expectedResult): void
    {
        $settings = ConfigurationUtility::mergeTypoScript2FlexForm($settings, $level);
        self::assertSame($expectedResult, $settings);
    }
}
