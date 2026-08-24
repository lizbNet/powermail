<?php

namespace In2code\Powermail\Tests\Unit\Domain\Model;

use In2code\Powermail\Tests\Unit\Fixtures\Domain\Model\FieldFixture;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class FieldTest
 */
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'optionArray')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'dataTypeFromFieldType')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getText')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setText')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getSettings')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setSettings')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getPrefillValue')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setPrefillValue')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getPlaceholder')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setPlaceholder')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getPlaceholderRepeat')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setPlaceholderRepeat')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getCreateFromTyposcript')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setCreateFromTyposcript')]
class FieldTest extends UnitTestCase
{
    /**
     * @var FieldFixture
     */
    protected $generalValidatorMock;

    public function setUp(): void
    {
        $this->generalValidatorMock = $this->getAccessibleMock(FieldFixture::class, null);
    }

    public function tearDown(): void
    {
        unset($this->generalValidatorMock);
    }

    public static function optionArrayReturnsArrayDataProvider(): array
    {
        return [
            [
                'abc',
                [
                    [
                        'label' => 'abc',
                        'value' => 'abc',
                        'selected' => 0,
                    ],
                ],
            ],
            [
                "red\nblue\nyellow",
                [
                    [
                        'label' => 'red',
                        'value' => 'red',
                        'selected' => 0,
                    ],
                    [
                        'label' => 'blue',
                        'value' => 'blue',
                        'selected' => 0,
                    ],
                    [
                        'label' => 'yellow',
                        'value' => 'yellow',
                        'selected' => 0,
                    ],
                ],
            ],
            [
                "please choose...|\nred\nblue|blue|*",
                [
                    [
                        'label' => 'please choose...',
                        'value' => '',
                        'selected' => 0,
                    ],
                    [
                        'label' => 'red',
                        'value' => 'red',
                        'selected' => 0,
                    ],
                    [
                        'label' => 'blue',
                        'value' => 'blue',
                        'selected' => 1,
                    ],
                ],
            ],
            [
                "||*\nred|red shoes",
                [
                    [
                        'label' => '',
                        'value' => '',
                        'selected' => 1,
                    ],
                    [
                        'label' => 'red',
                        'value' => 'red shoes',
                        'selected' => 0,
                    ],
                ],
            ],
            [
                "Red Shoes | 1 \nBlack Shoes | 2 | *\nBlue Shoes | ",
                [
                    [
                        'label' => 'Red Shoes',
                        'value' => '1',
                        'selected' => 0,
                    ],
                    [
                        'label' => 'Black Shoes',
                        'value' => '2',
                        'selected' => 1,
                    ],
                    [
                        'label' => 'Blue Shoes',
                        'value' => '',
                        'selected' => 0,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string $value
     * @param array $expectedResult
     */
    #[DataProvider('optionArrayReturnsArrayDataProvider')]
    #[Test]
    public function optionArrayReturnsArray($value, $expectedResult): void
    {
        $result = $this->generalValidatorMock->_call('optionArray', $value, '', false);
        self::assertSame($expectedResult, $result);
    }

    public static function dataTypeFromFieldTypeReturnsStringDataProvider(): array
    {
        return [
            [
                'captcha',
                0,
            ],
            [
                'check',
                1,
            ],
            [
                'file',
                3,
            ],
            [
                'input',
                0,
            ],
            [
                'textarea',
                0,
            ],
            [
                'select',
                0,
            ],
            [
                'select',
                1,
                true,
            ],
        ];
    }

    /**
     * @param string $fieldType
     * @param array $expectedResult
     * @param bool $multiple
     */
    #[DataProvider('dataTypeFromFieldTypeReturnsStringDataProvider')]
    #[Test]
    public function dataTypeFromFieldTypeReturnsString($fieldType, $expectedResult, $multiple = false): void
    {
        if ($multiple) {
            $this->generalValidatorMock->_set('multiselect', $multiple);
        }

        $result = $this->generalValidatorMock->_call('dataTypeFromFieldType', $fieldType);
        self::assertSame($expectedResult, $result);
    }

    /**
     * All of these columns are declared as bare `text` in ext_tables.sql
     * (settings, text, prefill_value, placeholder, placeholder_repeat,
     * create_from_typoscript), which cannot carry a DB-level NOT NULL
     * DEFAULT (a MySQL TEXT/BLOB limitation TYPO3's own
     * nullToDefaultUpdateWizard exists to clean up). Rows created outside
     * the normal FormEngine save path -- an import, or
     * Hook\CreateMarker::getFieldObjectFromProperties() hydrating straight
     * from a raw DB row via _setProperty() -- can therefore still persist
     * or assign a literal NULL, which fatals on a non-nullable native
     * `string` property. Each getter must keep returning a plain string
     * either way.
     */
    public static function nullableTextPropertiesDataProvider(): array
    {
        return [
            ['setText', 'getText'],
            ['setSettings', 'getSettings'],
            ['setPrefillValue', 'getPrefillValue'],
            ['setPlaceholder', 'getPlaceholder'],
            ['setPlaceholderRepeat', 'getPlaceholderRepeat'],
            ['setCreateFromTyposcript', 'getCreateFromTyposcript'],
        ];
    }

    #[DataProvider('nullableTextPropertiesDataProvider')]
    #[Test]
    public function setterAcceptsNullAndGetterReturnsEmptyString(string $setter, string $getter): void
    {
        $field = new \In2code\Powermail\Domain\Model\Field();
        $field->$setter(null);
        self::assertSame('', $field->$getter());
    }
}
