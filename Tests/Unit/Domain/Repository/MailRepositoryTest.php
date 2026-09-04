<?php

namespace In2code\Powermail\Tests\Unit\Domain\Repository;

use In2code\Powermail\Domain\Model\Answer;
use In2code\Powermail\Domain\Model\Field;
use In2code\Powermail\Domain\Model\Mail;
use In2code\Powermail\Domain\Repository\MailRepository;
use In2code\Powermail\Tests\Helper\TestingHelper;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class MailRepositoryTest
 */
#[CoversMethod(\In2code\Powermail\Domain\Repository\MailRepository::class, 'getLabelsWithMarkersFromMail')]
#[CoversMethod(\In2code\Powermail\Domain\Repository\MailRepository::class, 'getSenderMailFromArguments')]
#[CoversMethod(\In2code\Powermail\Domain\Repository\MailRepository::class, 'glueAnswerValues')]
#[CoversMethod(\In2code\Powermail\Domain\Repository\MailRepository::class, 'cleanStringForQuery')]
class MailRepositoryTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    /**
     * @var MailRepository
     */
    protected $generalValidatorMock;

    public function setUp(): void
    {
        parent::setUp();
        TestingHelper::setDefaultConstants();
        $objectManager = TestingHelper::getObjectManager();
        $this->generalValidatorMock = $this->getAccessibleMock(
            MailRepository::class,
            null,
            [$objectManager],
        );
    }

    public static function getLabelsWithMarkersFromMailReturnsArrayDataProvider(): array
    {
        return [
            [
                [
                    [
                        'marker',
                        'title',
                    ],
                ],
                [
                    'label_marker' => 'title',
                ],
            ],
            [
                [
                    [
                        'firstname',
                        'Firstname',
                    ],
                    [
                        'lastname',
                        'Lastname',
                    ],
                    [
                        'email',
                        'Email Address',
                    ],
                ],
                [
                    'label_firstname' => 'Firstname',
                    'label_lastname' => 'Lastname',
                    'label_email' => 'Email Address',
                ],
            ],
        ];
    }

    /**
     * @param array $values
     * @param string $expectedResult
     */
    #[DataProvider('getLabelsWithMarkersFromMailReturnsArrayDataProvider')]
    #[Test]
    public function getLabelsWithMarkersFromMailReturnsArray($values, $expectedResult): void
    {
        $mail = new Mail();
        if (is_array($values)) {
            foreach ($values as $markerTitleMix) {
                $answer = new Answer();
                $field = new Field();
                $field->setMarker($markerTitleMix[0]);
                $field->setTitle($markerTitleMix[1]);
                $answer->setField($field);
                $mail->addAnswer($answer);
            }
        }

        $result = $this->generalValidatorMock->_call('getLabelsWithMarkersFromMail', $mail);
        self::assertSame($expectedResult, $result);
    }

    public static function getSenderMailFromArgumentsReturnsStringDataProvider(): array
    {
        return [
            [
                [
                    'no email',
                    'abc@def.gh',
                ],
                '',
                null,
                'abc@def.gh',
            ],
            [
                [
                    'alexander.kellner@in2code.de',
                    'abc@def.gh',
                ],
                '',
                null,
                'alexander.kellner@in2code.de',
            ],
            [
                [
                    'no email',
                ],
                'test1@email.org',
                'test2@email.org',
                'test1@email.org',
            ],
            [
                [
                    'no email',
                ],
                'test1@email.org',
                null,
                'test1@email.org',
            ],
            [
                [
                    'no email',
                ],
                '',
                'test2@email.org',
                'test2@email.org',
            ],
            [
                [
                    'abc',
                    'def',
                    'ghi',
                ],
                'test1@email.org',
                'test2@email.org',
                'test1@email.org',
            ],
        ];
    }

    /**
     * @param array $values
     * @param string $fallback
     * @param string $defaultMailFromAddress
     * @param string $expectedResult
     */
    #[DataProvider('getSenderMailFromArgumentsReturnsStringDataProvider')]
    #[Test]
    public function getSenderMailFromArgumentsReturnsString(
        $values,
        $fallback,
        $defaultMailFromAddress,
        $expectedResult
    ): void {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $defaultMailFromAddress;
        $mail = new Mail();
        if (is_array($values)) {
            foreach ($values as $value) {
                $answer = new Answer();
                $answer->_setProperty('value', $value);
                $answer->_setProperty('valueType', (is_array($values) ? 2 : 0));
                $field = new Field();
                $field->setType('input');
                $field->setSenderEmail(true);
                $answer->setField($field);
                $mail->addAnswer($answer);
            }
        }

        $result = $this->generalValidatorMock->_call('getSenderMailFromArguments', $mail, $fallback);
        self::assertSame($expectedResult, $result);
    }

    public static function getSenderNameFromArgumentsReturnsStringDataProvider(): array
    {
        return [
            [
                [
                    'Alex',
                    'Kellner',
                ],
                null,
                null,
                'Alex Kellner',
            ],
            [
                null,
                null,
                'Fallback Name',
                'Fallback Name',
            ],
            [
                null,
                'Fallback Name',
                null,
                'Fallback Name',
            ],
            // The submitted value is returned verbatim on purpose. It is persisted, shown in the
            // backend module and exported, so it must not be mangled here. Keeping it out of a Fluid
            // template source is the job of SendMailService::getKeysAllowedToContainFluid().
            [
                ['{f:cObject(typoscriptObjectPath:\'lib.evil\')}'],
                null,
                null,
                '{f:cObject(typoscriptObjectPath:\'lib.evil\')}',
            ],
        ];
    }

    /**
     * @param array $values
     * @param string $fallback
     * @param string $defaultMailFromAddress
     * @param string $expectedResult
     */
    #[DataProvider('getSenderNameFromArgumentsReturnsStringDataProvider')]
    #[Test]
    public function getSenderNameFromArgumentsReturnsString(
        $values,
        $fallback,
        $defaultMailFromAddress,
        $expectedResult
    ): void {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $defaultMailFromAddress;
        $mail = new Mail();
        if (is_array($values)) {
            foreach ($values as $value) {
                $answer = new Answer();
                $answer->_setProperty('translateFormat', 'Y-m-d');
                $answer->_setProperty('valueType', (is_array($values) ? 2 : 0));
                $field = new Field();
                $field->setType('input');
                $field->setSenderName(true);
                $answer->_setProperty('value', $value);
                $answer->setValueType((is_array($value) ? 1 : 0));
                $answer->setField($field);
                $mail->addAnswer($answer);
            }
        }

        $result = $this->generalValidatorMock->_call('getSenderNameFromArguments', $mail, $fallback);
        self::assertSame($expectedResult, $result);
    }

    public static function glueAnswerValuesReturnsStringDataProvider(): array
    {
        return [
            [
                [
                    'Alex',
                    'Kellner',
                ],
                'Alex Kellner',
            ],
            [
                [
                    'Prof. Dr.',
                    'Müller',
                ],
                'Prof. Dr. Müller',
            ],
            [
                'Fallback Name',
                'Fallback Name',
            ],
            [
                [
                    'Prof.',
                    'Dr.',
                ],
                'Prof. Dr.',
            ],
        ];
    }

    /**
     * @param array|string $value
     * @param string $expectedResult
     */
    #[DataProvider('glueAnswerValuesReturnsStringDataProvider')]
    #[Test]
    public function glueAnswerValuesReturnsString(array|string $value, string $expectedResult): void
    {
        $result = $this->generalValidatorMock->_call('glueAnswerValues', $value, ' ');
        self::assertSame($expectedResult, $result);
    }

    #[Test]
    public function cleanStringForQueryReturnsString(): void
    {
        $str = '1a2b3+üßT$st';
        $result = $this->generalValidatorMock->_call('cleanStringForQuery', $str);
        self::assertSame('1a2b3Tst', $result);
    }
}
