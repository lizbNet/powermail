<?php

namespace In2code\Powermail\Tests\Unit\Utility;

use In2code\Powermail\Exception\ConfigurationIsMissingException;
use In2code\Powermail\Tests\Unit\Fixtures\Utility\HashUtilityFixture;
use In2code\Powermail\Utility\HashUtility;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class HashUtilityTest
 */
#[CoversMethod(HashUtility::class, 'getEncryptionKey')]
class HashUtilityTest extends UnitTestCase
{
    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     * @throws ConfigurationIsMissingException
     */
    public function testGetEncryptionKey(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'abcdef';
        self::assertSame('abcdef', HashUtilityFixture::getEncryptionKeyForTesting());

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = '';
        $this->expectExceptionCode(1514910284796);
        HashUtilityFixture::getEncryptionKeyForTesting();
    }
}
