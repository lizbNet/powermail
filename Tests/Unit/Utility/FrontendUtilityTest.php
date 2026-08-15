<?php

namespace In2code\Powermail\Tests\Unit\Utility;

use In2code\Powermail\Tests\Helper\TestingHelper;
use In2code\Powermail\Utility\FrontendUtility;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class FrontendUtilityTest
 */
#[CoversMethod(FrontendUtility::class, 'getDomainFromUri')]
#[CoversMethod(FrontendUtility::class, 'getCountryFromIp')]
#[CoversMethod(FrontendUtility::class, 'getSubFolderOfCurrentUrl')]
class FrontendUtilityTest extends UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    /**
     * Data Provider for getDomainFromUriReturnsString()
     */
    public static function getDomainFromUriReturnsStringDataProvider(): array
    {
        return [
            [
                'http://subdomain.domain.org/folder/file.html',
                'subdomain.domain.org',
            ],
            [
                'ftp://domain.org',
                'domain.org',
            ],
            [
                'https://www.domain.co.uk/',
                'www.domain.co.uk',
            ],
        ];
    }

    /**
     * @param string $value
     * @param string $expectedResult
     */
    #[DataProvider('getDomainFromUriReturnsStringDataProvider')]
    #[Test]
    public function getDomainFromUriReturnsString($value, $expectedResult): void
    {
        self::assertSame($expectedResult, FrontendUtility::getDomainFromUri($value));
    }

    /**
     * Data Provider for getCountryFromIpReturnsString()
     */
    public static function getCountryFromIpReturnsStringDataProvider(): array
    {
        return [
            [
                '217.72.208.133',
                'Germany',
            ],
            [
                '27.121.255.4',
                'Japan',
            ],
            [
                '5.226.31.255',
                'Spain',
            ],
            [
                '66.85.131.18',
                'United States',
            ],
            [
                '182.118.23.7',
                'China',
            ],
        ];
    }

    /**
     * @param string $ipAddress
     * @param string $expectedResult
     */
    #[DataProvider('getCountryFromIpReturnsStringDataProvider')]
    #[Test]
    public function getCountryFromIpReturnsString($ipAddress, $expectedResult): void
    {
        self::assertSame($expectedResult, FrontendUtility::getCountryFromIp($ipAddress));
    }

    /**
     * Dataprovider getSubFolderOfCurrentUrlReturnsString()
     */
    public static function getSubFolderOfCurrentUrlReturnsStringDataProvider(): array
    {
        return [
            [
                true,
                true,
                'http://www.in2code.de',
                'http://www.in2code.de/',
                '/',
            ],
            [
                false,
                true,
                'http://www.in2code.de',
                'http://www.in2code.de/',
                '/',
            ],
            [
                true,
                false,
                'http://www.in2code.de',
                'http://www.in2code.de/',
                '/',
            ],
            [
                false,
                false,
                'http://www.in2code.de',
                'http://www.in2code.de/',
                '',
            ],
            [
                true,
                true,
                'http://www.in2code.de',
                'http://www.in2code.de/subfolder/',
                '/subfolder/',
            ],
            [
                false,
                true,
                'http://www.in2code.de',
                'http://www.in2code.de/subfolder/',
                'subfolder/',
            ],
            [
                true,
                false,
                'http://www.in2code.de',
                'http://www.in2code.de/subfolder/',
                '/subfolder',
            ],
            [
                false,
                false,
                'http://www.in2code.de',
                'http://www.in2code.de/subfolder/',
                'subfolder',
            ],
        ];
    }

    /**
     * @param bool $leadingSlash will be prepended
     * @param bool $trailingSlash will be appended
     * @param string $host
     * @param string $url
     * @param string $expectedResult
     */
    #[DataProvider('getSubFolderOfCurrentUrlReturnsStringDataProvider')]
    #[Test]
    public function getSubFolderOfCurrentUrlReturnsString($leadingSlash, $trailingSlash, $host, $url, $expectedResult): void
    {
        $result = FrontendUtility::getSubFolderOfCurrentUrl($leadingSlash, $trailingSlash, $host, $url);
        self::assertSame($expectedResult, $result);
    }
}
