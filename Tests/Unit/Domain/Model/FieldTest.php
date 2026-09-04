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
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getTitle')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setTitle')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getPath')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setPath')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getValidationConfiguration')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setValidationConfiguration')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getCss')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setCss')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getDescription')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setDescription')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getFeuserValue')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setFeuserValue')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getMandatoryText')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setMandatoryText')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getMarkerOriginal')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setMarker')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getAutocompleteToken')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setAutocompleteToken')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getAutocompleteSection')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setAutocompleteSection')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getAutocompleteType')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setAutocompleteType')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getAutocompletePurpose')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setAutocompletePurpose')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getType')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setType')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getDatepickerSettings')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'setDatepickerSettings')]
#[CoversMethod(\In2code\Powermail\Domain\Model\Field::class, 'getMarker')]
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
     * Hook\CreateMarker::getFieldObjectFromProperties() hydrates a Field
     * straight from a raw DB row via _setProperty() on every single
     * backend save, not just on submit. Several string-typed columns on
     * this table (declared `text` in ext_tables.sql, which can't carry a
     * real DB-level NOT NULL DEFAULT for MySQL TEXT/BLOB types -- a
     * MySQL limitation TYPO3's own nullToDefaultUpdateWizard exists to
     * clean up -- or drifted nullable over years of production schema
     * history despite their declared type) have each, one at a time,
     * turned up as a literal NULL in production and fataled every save
     * attempt on the affected field row: "Cannot assign null to property
     * ...::$x of type string". Every plain string property on this model
     * is nullable for exactly this reason -- each getter must keep
     * returning a plain string regardless of what CreateMarker assigns.
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
            ['setTitle', 'getTitle'],
            ['setPath', 'getPath'],
            ['setValidationConfiguration', 'getValidationConfiguration'],
            ['setCss', 'getCss'],
            ['setDescription', 'getDescription'],
            ['setFeuserValue', 'getFeuserValue'],
            ['setMandatoryText', 'getMandatoryText'],
            ['setMarker', 'getMarkerOriginal'],
            ['setAutocompleteToken', 'getAutocompleteToken'],
            ['setAutocompleteSection', 'getAutocompleteSection'],
            ['setAutocompleteType', 'getAutocompleteType'],
            ['setAutocompletePurpose', 'getAutocompletePurpose'],
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

    /**
     * getType() has its own fallback (defaults to "input") instead of a
     * plain empty string -- covered separately from the generic
     * empty-string data provider above.
     */
    #[Test]
    public function setTypeAcceptsNullAndGetTypeReturnsInput(): void
    {
        $field = new \In2code\Powermail\Domain\Model\Field();
        $field->setType(null);
        self::assertSame('input', $field->getType());
    }

    /**
     * getDatepickerSettings() has its own fallback (defaults to "date")
     * instead of a plain empty string.
     */
    #[Test]
    public function setDatepickerSettingsAcceptsNullAndGetDatepickerSettingsReturnsDate(): void
    {
        $field = new \In2code\Powermail\Domain\Model\Field();
        $field->setDatepickerSettings(null);
        self::assertSame('date', $field->getDatepickerSettings());
    }

    /**
     * getMarker() already treats an empty marker as "no marker set" via
     * empty(), which is true for null too, so it falls through to its
     * "uid{uid}" fallback rather than crashing.
     */
    #[Test]
    public function setMarkerAcceptsNullAndGetMarkerFallsBackToUid(): void
    {
        $field = new \In2code\Powermail\Domain\Model\Field();
        $field->setMarker(null);
        self::assertSame('uid', $field->getMarker());
    }
}
