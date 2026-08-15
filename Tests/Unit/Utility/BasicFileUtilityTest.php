<?php

namespace In2code\Powermail\Tests\Unit\Utility;

use In2code\Powermail\Exception\FileCannotBeCreatedException;
use In2code\Powermail\Tests\Helper\TestingHelper;
use In2code\Powermail\Utility\BasicFileUtility;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class BasicFileUtiltyTest
 */
#[CoversMethod(BasicFileUtility::class, 'getFilesFromRelativePath')]
#[CoversMethod(BasicFileUtility::class, 'getPathFromPathAndFilename')]
#[CoversMethod(BasicFileUtility::class, 'createFolderIfNotExists')]
#[CoversMethod(BasicFileUtility::class, 'prependContentToFile')]
#[CoversMethod(BasicFileUtility::class, 'getRelativeFolder')]
class BasicFileUtilityTest extends UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
    }

    #[Test]
    public function getFilesFromRelativePathReturnsString(): void
    {
        $testpath = TestingHelper::getWebRoot() . 'fileadmin/getFilesFromRelativePathReturnsString/';
        BasicFileUtility::createFolderIfNotExists($testpath);
        touch($testpath . 'aaa.txt');
        touch($testpath . 'bbb.txt');

        $result = BasicFileUtility::getFilesFromRelativePath('fileadmin/getFilesFromRelativePathReturnsString/');
        GeneralUtility::rmdir($testpath, true);

        self::assertSame(['aaa.txt', 'bbb.txt'], $result);
    }

    #[Test]
    public function getPathFromPathAndFilenameReturnsString(): void
    {
        $result = BasicFileUtility::getPathFromPathAndFilename('typo3/index.php');
        self::assertSame('typo3', $result);
    }

    /**
     * @throws FileCannotBeCreatedException
     */
    #[Test]
    public function createFolderIfNotExistsReturnsVoid(): void
    {
        $testpath = TestingHelper::getWebRoot() . 'fileadmin/';

        BasicFileUtility::createFolderIfNotExists($testpath);
        self::assertDirectoryExists($testpath);
        GeneralUtility::rmdir($testpath);
    }

    /**
     * @throws FileCannotBeCreatedException
     */
    #[Test]
    public function prependContentToFileReturnsVoid(): void
    {
        $testpath = TestingHelper::getWebRoot() . 'fileadmin/';
        BasicFileUtility::createFolderIfNotExists($testpath);
        $fileName = $testpath . 'unittest.txt';

        BasicFileUtility::prependContentToFile($fileName, 'abc');
        BasicFileUtility::prependContentToFile($fileName, 'def');
        $content = file($fileName);
        GeneralUtility::rmdir($testpath, true);
        self::assertSame(['defabc'], $content);
    }

    #[Test]
    public function getRelativeFolderReturnsString(): void
    {
        $testPath = 'typo3conf/ext/powermail/';
        self::assertStringEndsWith(
            $testPath,
            BasicFileUtility::getRelativeFolder(TestingHelper::getWebRoot() . $testPath)
        );
        self::assertSame($testPath, BasicFileUtility::getRelativeFolder($testPath));
    }
}
