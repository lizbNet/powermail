<?php

namespace In2code\Powermail\Tests\Unit\Utility;

use In2code\Powermail\Utility\AbstractUtility;
use In2code\Powermail\Utility\ObjectUtility;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class ObjectUtilityTest
 */
#[CoversMethod(ObjectUtility::class, 'getFilesArray')]
#[CoversMethod(AbstractUtility::class, 'getFilesArray')]
#[CoversMethod(ObjectUtility::class, 'getLogger')]
class ObjectUtilityTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    #[Test]
    public function getFilesArray(): void
    {
        $result = ObjectUtility::getFilesArray();
        self::assertTrue(is_array($result));
    }

    public function testGetLogger(): void
    {
        $logger = ObjectUtility::getLogger(self::class);
        self::assertInstanceOf(Logger::class, $logger);
    }
}
