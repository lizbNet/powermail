<?php

namespace In2code\Powermail\Tests\Unit\ViewHelpers\String;

use In2code\Powermail\Tests\Unit\Fixtures\ViewHelpers\String\TrimViewHelperFixture;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class TrimViewHelperTest
 */
#[CoversMethod(\In2code\Powermail\ViewHelpers\String\TrimViewHelper::class, 'render')]
#[CoversMethod(\In2code\Powermail\ViewHelpers\String\TrimViewHelper::class, 'removeDuplicatedWhitespace')]
class TrimViewHelperTest extends UnitTestCase
{
    /**
     * @var TrimViewHelperFixture
     */
    protected $trimViewHelperMock;

    public function setUp(): void
    {
        $this->trimViewHelperMock = $this->getAccessibleMock(
            TrimViewHelperFixture::class,
            null
        );
    }

    public function tearDown(): void
    {
        unset($this->trimViewHelperMock);
    }

    /**
     * Dataprovider for renderReturnsString()
     */
    public static function renderReturnsStringDataProvider(): array
    {
        return [
            [
                ' abc   ',
                'abc',
            ],
            [
                '	abc	',
                'abc',
            ],
            [
                'a 	  b 	 c',
                'a b c',
            ],
            [
                'a				b			c',
                'a b c',
            ],
            [
                '"a" ; "b" ;"c"; "d"',
                '"a";"b";"c";"d"',
            ],
            [
                '<br/><br><br />',
                '',
            ],
            [
                " \n " . ',' . "\n " . ',' . " \n",
                ', ,',
            ],
            [
                ' "name" ;"firstname" ;  "email"; <br>    <br>    <br> <br> ' . "\t\t\n\n" .
                    ' "name";"firstname";"email"; <br />' .
                    '   "name";"email";   ',
                '"name";"firstname";"email";' . PHP_EOL .
                    '"name";"firstname";"email";' . PHP_EOL .
                    '"name";"email";',
            ],
        ];
    }

    /**
     * @param string $string
     * @param string $expectedResult
     */
    #[Test]
    #[DataProvider('renderReturnsStringDataProvider')]
    public function renderReturnsString($string, $expectedResult): void
    {
        $this->trimViewHelperMock->_set('renderChildrenString', $string);
        self::assertSame($expectedResult, $this->trimViewHelperMock->_call('render'));
    }

    /**
     * Dataprovider for removeDuplicatedWhitespaceReturnsString()
     */
    public static function removeDuplicatedWhitespaceReturnsStringDataProvider(): array
    {
        return [
            [
                '  abc    ',
                ' abc ',
            ],
            [
                'a' . PHP_EOL . PHP_EOL . 'b',
                'a b',
            ],
            [
                "\t\na\t\n",
                ' a ',
            ],
        ];
    }

    /**
     * @param string $string
     * @param string $expectedResult
     */
    #[Test]
    #[DataProvider('removeDuplicatedWhitespaceReturnsStringDataProvider')]
    public function removeDuplicatedWhitespaceReturnsString($string, $expectedResult): void
    {
        self::assertSame($expectedResult, $this->trimViewHelperMock->_call('removeDuplicatedWhitespace', $string));
    }
}
