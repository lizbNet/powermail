<?php

namespace In2code\Powermail\Tests\Unit\Utility;

use In2code\Powermail\Domain\Model\Answer;
use In2code\Powermail\Domain\Model\Field;
use In2code\Powermail\Domain\Model\Mail;
use In2code\Powermail\Utility\ReportingUtility;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Reflection\Exception\PropertyNotAccessibleException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class ReportingUtilityTest
 */
#[CoversMethod(ReportingUtility::class, 'getGroupedAnswersFromMails')]
#[CoversMethod(ReportingUtility::class, 'getGroupedMarketingPropertiesFromMails')]
#[CoversMethod(ReportingUtility::class, 'sortReportingArrayDescending')]
#[CoversMethod(ReportingUtility::class, 'cutArrayByKeyLimitAndAddTotalValues')]
class ReportingUtilityTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    #[Test]
    public function getGroupedAnswersFromMailsReturnsArray(): void
    {
        $result = ReportingUtility::getGroupedAnswersFromMails($this->getDummyMails());
        $expected = [
            123 => [
                'abc' => 4,
            ],
        ];
        self::assertSame($expected, $result);
    }

    /**
     * @throws PropertyNotAccessibleException
     */
    #[Test]
    public function getGroupedMarketingPropertiesFromMailsReturnsArray(): void
    {
        $result = ReportingUtility::getGroupedMarketingPropertiesFromMails($this->getDummyMails());
        $expected = [
            'marketingRefererDomain' => [
                '-' => 4,
            ],
            'marketingReferer' => [
                '-' => 4,
            ],
            'marketingCountry' => [
                '-' => 4,
            ],
            'marketingMobileDevice' => [
                '-' => 4,
            ],
            'marketingFrontendLanguage' => [
                '-' => 4,
            ],
            'marketingBrowserLanguage' => [
                '-' => 4,
            ],
            'marketingPageFunnelString' => [
                '-' => 4,
            ],
        ];
        self::assertSame($expected, $result);
    }

    /**
     * @return Mail[]
     */
    protected function getDummyMails()
    {
        $mails = [];
        for ($i = 0; $i < 4; $i++) {
            $field = new Field();
            $field->_setProperty('uid', 123);
            $answer = new Answer();
            $answer->setField($field);
            $answer->setValue('abc');
            $mail = new Mail();
            $mail->addAnswer($answer);
            $mails[] = $mail;
        }

        return $mails;
    }

    /**
     * Data Provider for sortReportingArrayDescendingReturnsVoid()
     */
    public static function sortReportingArrayDescendingReturnsVoidDataProvider(): array
    {
        return [
            [
                [
                    [
                        'blue' => 5,
                        'black' => 1,
                        'red' => 2,
                        'yellow' => 9,
                    ],
                ],
                [
                    [
                        'yellow' => 9,
                        'blue' => 5,
                        'red' => 2,
                        'black' => 1,
                    ],
                ],
            ],
            [
                [
                    [
                        'a' => 5,
                        '' => 11,
                        '23' => 2,
                        'x ' => 9,
                    ],
                ],
                [
                    [
                        '' => 11,
                        'x ' => 9,
                        'a' => 5,
                        '23' => 2,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $array
     * @param array $expectedResult
     */
    #[DataProvider('sortReportingArrayDescendingReturnsVoidDataProvider')]
    #[Test]
    public function sortReportingArrayDescendingReturnsVoid($array, $expectedResult): void
    {
        ReportingUtility::sortReportingArrayDescending($array);
        self::assertSame($array, $expectedResult);
    }

    /**
     * Data Provider for cutArrayByKeyLimitAndAddTotalValuesReturnsVoid()
     */
    public static function cutArrayByKeyLimitAndAddTotalValuesReturnsVoidDataProvider(): array
    {
        return [
            [
                [
                    [
                        'blue' => 5,
                        'black' => 1,
                        'red' => 2,
                        'yellow' => 9,
                    ],
                ],
                [
                    [
                        'blue' => 5,
                        'black' => 1,
                        'others' => 11,
                    ],
                ],
            ],
            [
                [
                    [
                        'blue' => 2,
                        'black' => 3,
                        'red' => 4,
                        'yellow' => 5,
                        'brown' => 6,
                        'pink' => 7,
                        'orange' => 8,
                        'violet' => 9,
                        'green' => 3,
                    ],
                ],
                [
                    [
                        'blue' => 2,
                        'black' => 3,
                        'others' => 42,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $array
     * @param array $expectedResult
     */
    #[DataProvider('cutArrayByKeyLimitAndAddTotalValuesReturnsVoidDataProvider')]
    #[Test]
    public function cutArrayByKeyLimitAndAddTotalValuesReturnsVoid($array, $expectedResult): void
    {
        ReportingUtility::cutArrayByKeyLimitAndAddTotalValues($array, 3, 'others');
        self::assertSame($array, $expectedResult);
    }
}
