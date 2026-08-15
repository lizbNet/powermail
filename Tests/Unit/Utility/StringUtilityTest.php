<?php

namespace In2code\Powermail\Tests\Unit\Utility;

use In2code\Powermail\Utility\StringUtility;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class StringUtilityTest
 */
#[CoversMethod(StringUtility::class, 'isNotEmpty')]
#[CoversMethod(StringUtility::class, 'getRandomString')]
#[CoversMethod(StringUtility::class, 'conditionalVariable')]
#[CoversMethod(StringUtility::class, 'endsWith')]
#[CoversMethod(StringUtility::class, 'startsWith')]
#[CoversMethod(StringUtility::class, 'removeLastDot')]
#[CoversMethod(StringUtility::class, 'br2nl')]
#[CoversMethod(StringUtility::class, 'getStringLength')]
#[CoversMethod(StringUtility::class, 'cleanString')]
#[CoversMethod(StringUtility::class, 'integerList')]
#[CoversMethod(StringUtility::class, 'getSrcFromImageTag')]
#[CoversMethod(StringUtility::class, 'addTrailingSlash')]
class StringUtilityTest extends UnitTestCase
{
    /**
     * Dataprovider isNotEmptyReturnsBool()
     */
    public static function isNotEmptyReturnsBoolDataProvider(): array
    {
        return [
            'string "in2code.de"' => [
                'in2code.de',
                true,
            ],
            'string "a"' => [
                'a',
                true,
            ],
            'string empty' => [
                '',
                false,
            ],
            'string "0"' => [
                '0',
                true,
            ],
            'int 0' => [
                0,
                true,
            ],
            'int 1' => [
                1,
                true,
            ],
            'float 0.0' => [
                0.0,
                true,
            ],
            'float 1.0' => [
                1.0,
                true,
            ],
            'null' => [
                null,
                false,
            ],
            'bool false' => [
                false,
                false,
            ],
            'bool true' => [
                true,
                false,
            ],
            'array: string empty' => [
                [''],
                false,
            ],
            'array: int 0' => [
                [0],
                true,
            ],
            'array: int 1' => [
                [1],
                true,
            ],
            'array: "abc" => "def"' => [
                ['abc' => 'def'],
                true,
            ],
            'array: empty' => [
                [],
                false,
            ],
        ];
    }

    /**
     * @param string $value
     * @param array $expectedResult
     */
    #[DataProvider('isNotEmptyReturnsBoolDataProvider')]
    #[Test]
    public function isNotEmptyReturnsBool($value, $expectedResult): void
    {
        self::assertSame($expectedResult, StringUtility::isNotEmpty($value));
    }

    /**
     * Data Provider for getRandomStringAlwaysReturnsStringsOfGivenLength
     */
    public static function getRandomStringAlwaysReturnsStringsOfGivenLengthDataProvider(): array
    {
        return [
            'default params' => [
                32,
                true,
            ],
            'default length lowercase' => [
                32,
                false,
            ],
            '60 length' => [
                60,
                true,
            ],
            '60 length lowercase' => [
                60,
                false,
            ],
        ];
    }

    /**
     * getRandomStringAlwaysReturnsStringsOfGivenLength Test
     *
     * @param int $length
     * @param bool $uppercase
     */
    #[DataProvider('getRandomStringAlwaysReturnsStringsOfGivenLengthDataProvider')]
    #[Test]
    public function getRandomStringAlwaysReturnsStringsOfGivenLength($length, $uppercase): void
    {
        for ($i = 0; $i < 100; $i++) {
            $string = StringUtility::getRandomString($length, $uppercase);

            $regex = '~[a-z0-9]{' . $length . '}~';
            if ($uppercase) {
                $regex = '~[a-zA-Z0-9]{' . $length . '}~';
            }

            self::assertSame(1, preg_match($regex, $string));
        }
    }

    /**
     * Data Provider for conditionalVariableReturnsMixed()
     */
    public static function conditionalVariableReturnsMixedDataProvider(): array
    {
        return [
            [
                'string',
                'fallbackstring',
                'string',
            ],
            [
                ['abc'],
                ['def'],
                ['abc'],
            ],
            [
                '',
                'fallback',
                'fallback',
            ],
            [
                null,
                true,
                true,
            ],
            [
                123,
                234,
                123,
            ],
        ];
    }

    #[DataProvider('conditionalVariableReturnsMixedDataProvider')]
    #[Test]
    public function conditionalVariableReturnsMixed(mixed $variable, mixed $fallback, mixed $expectedResult): void
    {
        self::assertSame($expectedResult, StringUtility::conditionalVariable($variable, $fallback));
    }

    /**
     * Data Provider for endsWithReturnsString()
     */
    public static function endsWithReturnsStringDataProvider(): array
    {
        return [
            [
                'xFinisher',
                'Finisher',
                true,
            ],
            [
                'inisher',
                'Finisher',
                false,
            ],
            [
                'abc',
                'c',
                true,
            ],
            [
                'abc',
                'bc',
                true,
            ],
            [
                'abc',
                'abc',
                true,
            ],
            [
                '/test//',
                '/',
                true,
            ],
            [
                '/test//x',
                '/',
                false,
            ],
        ];
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param bool $expectedResult
     */
    #[DataProvider('endsWithReturnsStringDataProvider')]
    #[Test]
    public function endsWithReturnsString($haystack, $needle, $expectedResult): void
    {
        self::assertSame($expectedResult, StringUtility::endsWith($haystack, $needle));
    }

    /**
     * Data Provider for startsWithReturnsString()
     */
    public static function startsWithReturnsStringDataProvider(): array
    {
        return [
            [
                'Finisherx',
                'Finisher',
                true,
            ],
            [
                'inisher',
                'Finisher',
                false,
            ],
            [
                'abc',
                'a',
                true,
            ],
            [
                'abc',
                'ab',
                true,
            ],
            [
                'abc',
                'abc',
                true,
            ],
        ];
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param bool $expectedResult
     */
    #[DataProvider('startsWithReturnsStringDataProvider')]
    #[Test]
    public function startsWithReturnsString($haystack, $needle, $expectedResult): void
    {
        self::assertSame($expectedResult, StringUtility::startsWith($haystack, $needle));
    }

    /**
     * Data Provider for removeLastDotReturnsString()
     */
    public static function removeLastDotReturnsStringDataProvider(): array
    {
        return [
            [
                'abc',
                'abc',
            ],
            [
                'abc.',
                'abc',
            ],
            [
                '.abc.',
                '.abc',
            ],
            [
                '.a.b.c.',
                '.a.b.c',
            ],
            [
                'abc..',
                'abc.',
            ],
        ];
    }

    /**
     * @param string $string
     * @param string $expectedResult
     */
    #[DataProvider('removeLastDotReturnsStringDataProvider')]
    #[Test]
    public function removeLastDotReturnsString($string, $expectedResult): void
    {
        self::assertSame($expectedResult, StringUtility::removeLastDot($string));
    }

    /**
     * Data Provider for br2nlReturnString()
     */
    public static function br2nlReturnStringDataProvider(): array
    {
        return [
            [
                'a<br>b',
                "a\nb",
            ],
            [
                'a<br><br /><br/>b',
                "a\n\n\nb",
            ],
            [
                'a\nbr[br]b',
                'a\nbr[br]b',
            ],
        ];
    }

    /**
     * @param string $content
     * @param string $expectedResult
     */
    #[DataProvider('br2nlReturnStringDataProvider')]
    #[Test]
    public function br2nlReturnString($content, $expectedResult): void
    {
        self::assertSame($expectedResult, StringUtility::br2nl($content));
    }

    /**
     * Data Provider for getStringLengthReturnInt()
     */
    public static function getStringLengthReturnIntDataProvider(): array
    {
        return [
            [
                'abc',
                3,
            ],
            [
                'äbc',
                3,
            ],
            [
                "a\nb",
                3,
            ],
            [
                "a\r\nb",
                3,
            ],
        ];
    }

    /**
     * @param string $string
     * @param int $expectedResult
     */
    #[DataProvider('getStringLengthReturnIntDataProvider')]
    #[Test]
    public function getStringLengthReturnInt($string, $expectedResult): void
    {
        self::assertSame($expectedResult, StringUtility::getStringLength($string));
    }

    #[Test]
    public function cleanStringReturnsString(): void
    {
        self::assertSame('iu.asd__________-3test', StringUtility::cleanString('iu.asd?ßü**^%_-3test'));
    }

    #[Test]
    public function integerListReturnsString(): void
    {
        self::assertSame('5,8,0', StringUtility::integerList('5,8,a4'));
        self::assertSame('5,8,4', StringUtility::integerList('5,8,4a'));
        self::assertSame('5,8,4', StringUtility::integerList('5,8,4'));
    }

    #[Test]
    public function getSrcFromImageTagReturnsString(): void
    {
        $tag = '<img id="ab3src" src="test.jpg" class="src=" data-action="test" />';
        self::assertSame('test.jpg', StringUtility::getSrcFromImageTag($tag));
    }

    public static function addTrailingSlashReturnStringDataProvider(): array
    {
        return [
            [
                'folder1/folder2',
                'folder1/folder2/',
            ],
            [
                'folder1/folder2/',
                'folder1/folder2/',
            ],
            [
                'folder1',
                'folder1/',
            ],
            [
                'folder1///',
                'folder1/',
            ],
            [
                '/fo/ld/er1//',
                '/fo/ld/er1/',
            ],
        ];
    }

    /**
     * @param string $string
     * @param string $expectedResult
     */
    #[DataProvider('addTrailingSlashReturnStringDataProvider')]
    public function testAddTrailingSlashReturnString($string, $expectedResult): void
    {
        self::assertSame($expectedResult, StringUtility::addTrailingSlash($string));
    }
}
