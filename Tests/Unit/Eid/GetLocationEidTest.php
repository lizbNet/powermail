<?php

namespace In2code\Powermail\Tests\Unit\Eid;

use In2code\Powermail\Eid\GetLocationEid;
use In2code\Powermail\Tests\Helper\TestingHelper;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class GetLocationEidTest
 */
#[CoversMethod(\In2code\Powermail\Eid\GetLocationEid::class, 'main')]
#[CoversMethod(\In2code\Powermail\Eid\GetLocationEid::class, 'getAddressFromGeo')]
class GetLocationEidTest extends UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    public static function mainDataProvider(): array
    {
        return [
            'in2code GmbH, Rosenheim, Germany' => [
                47.84787,
                12.113768,
                'Kunstmühlstraße',
            ],
            'Eisweiherweg, Forsting, Germany' => [
                48.0796126,
                12.0898908,
                'Eisweiherweg',
            ],
            'Baker Street, London, UK' => [
                51.5205573,
                -0.1566651,
                'Baker Street',
            ],
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    #[DataProvider('mainDataProvider')]
    public function testMain(float $latitude, float $longitude, string $expectedResult): void
    {
        $request = new ServerRequest();
        $request = $request->withQueryParams(
            [
                'lat' => $latitude,
                'lng' => $longitude,
            ]
        );
        $getLocationEid = new GetLocationEid();
        $response = $getLocationEid->main($request);
        self::assertSame(200, $response->getStatusCode());
        $stream = $response->getBody();
        $stream->rewind();
        self::assertStringContainsString($expectedResult, $stream->getContents());
    }
}
