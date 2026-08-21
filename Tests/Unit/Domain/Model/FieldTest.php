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
     * The "text" column is a TCA type=text field, which cannot carry a DB-level
     * NOT NULL DEFAULT (a MySQL TEXT/BLOB limitation TYPO3's own
     * nullToDefaultUpdateWizard exists to clean up). Rows created outside the
     * normal FormEngine save path can therefore still persist a literal NULL,
     * which Extbase's property mapper writes straight into this property
     * during hydration. getText() must keep returning a plain string either way.
     */
    #[Test]
    public function setTextAcceptsNullAndGetTextReturnsEmptyString(): void
    {
        $field = new \In2code\Powermail\Domain\Model\Field();
        $field->setText(null);
        self::assertSame('', $field->getText());
    }
}
