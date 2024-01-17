<?php

namespace Email\v3;

use ebitkov\Mailjet\Email\Contact;
use ebitkov\Mailjet\Email\v3\Attachment;
use ebitkov\Mailjet\Email\v3\Email;
use ebitkov\Mailjet\Email\v3\Recipient;
use ebitkov\Mailjet\Email\v3\SentEmail;
use ebitkov\Mailjet\RequestAborted;
use ebitkov\Mailjet\RequestFailed;
use ebitkov\Mailjet\Result;
use ebitkov\Mailjet\Tests\MailjetApiTestCase;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

use function PHPStan\dumpType;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertNotEmpty;

class EmailTest extends MailjetApiTestCase
{
    public static function getValidationData(): array
    {
        /**
         * 0: <Email> object to validate
         * 1: <callback>: tests before validation
         * 2: <int> expected violations
         * 3: <list<string>> expected violation messages
         */
        return [
            '"Recipients" and "To" not set' => [
                self::getBasicEmail()->clearRecipients(),
                function (Email $email) {
                    assertEmpty($email->recipients);
                    assertEmpty($email->getTo());
                },
                1,
                [
                    '"Recipients" or "To" is required.'
                ]
            ],
            '"Cc" combined with "Recipients"' => [
                self::getBasicEmail()->addCc(new Recipient('passenger@mailjet.com')),
                function (Email $email) {
                    assertNotEmpty($email->recipients);
                    assertNotEmpty($email->getCc());
                },
                1,
                [
                    '"Cc" can not be used together with "Recipients". Use "To" instead.'
                ]
            ],
            '"Bcc" combined with "Recipients"' => [
                self::getBasicEmail()->addBcc(new Recipient('passenger@mailjet.com')),
                function (Email $email) {
                    assertNotEmpty($email->recipients);
                    assertNotEmpty($email->getBcc());
                },
                1,
                [
                    '"Bcc" can not be used together with "Recipients". Use "To" instead.'
                ]
            ],
            'Invalid value for "Mj-TemplateErrorDeliver"' => [
                self::getBasicEmail()->setTemplateErrorDeliver('1'),
                null,
                1,
                [
                    'The value for "Mj-TemplateErrorDeliver" is not valid. Allowed are [ "deliver", "0" ].'
                ]
            ]
        ];
    }

    private static function getBasicEmail(): Email
    {
        return (new Email())
            ->setFromName('Your Mailjet Pilot')
            ->setFromEmail('pilot@mailjet.com')
            ->addRecipient(new Recipient('passenger@mailjet.com', 'Passenger 1'))
            ->setSubject('Your email flight plan!')
            ->setTextPart('Dear passenger, welcome to Mailjet! May the delivery force be with you!')
            ->setHtmlPart('<h3>Dear passenger, welcome to Mailjet!</h3><br />May the delivery force be with you!');
    }

    /**
     * @throws Exception
     * @throws ExceptionInterface
     * @throws RequestAborted
     * @throws RequestFailed
     */
    public function testSendEmail(): void
    {
        $client = $this->getClient();
        $email = $this->getBasicEmail();

        $result = $client->sendEmail($email);

        $this->assertInstanceOf(Result::class, $result);
        // the Send API does not return a total count
        $this->assertNull($result->totalCount);
        $this->assertSame(1, $result->count());

        $sentMail = $result->first();
        $this->assertInstanceOf(SentEmail::class, $sentMail);

        $this->assertSame('passenger@mailjet.com', $sentMail->email);
        $this->assertSame(1234567890987654400, $sentMail->messageId);
        $this->assertSame('1ab23cd4-e567-8901-2345-6789f0gh1i2j', $sentMail->messageUuid);
    }

    /**
     * @throws Exception
     * @throws RequestFailed
     * @throws RequestAborted
     * @throws ExceptionInterface
     */
    public function testSendInvalidEmail(): void
    {
        $client = $this->getClient();
        $email = $this->getBasicEmail()->clearRecipients();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('"Recipients" or "To" is required.');

        $client->sendEmail($email);
    }

    /**
     * This test ensures, that all arguments are properly normalized.
     *
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function testFullNormalization(): void
    {
        $client = $this->getClient();

        $email = new Email();

        $email
            ->setFromEmail('pilot@mailjet.com')
            ->setFromName('Your Mailjet Pilot')
            ->setSender(false)
            ->addRecipient(
                new Recipient(
                    'passenger@mailjet.com',
                    'Passenger 1',
                    [
                        'var1' => 'foo',
                        'var2' => 'bar'
                    ]
                )
            )
            ->addTo(new Recipient('passenger2@mailjet.com', 'Passenger 2'))
            ->addTo(new Recipient('passenger3@mailjet.com', 'Passenger 3'))
            ->addCc(new Recipient('passenger4@mailjet.com', 'Passenger 4'))
            ->addCc(new Recipient('passenger5@mailjet.com', 'Passenger 5'))
            ->addBcc(new Recipient('passenger6@mailjet.com', 'Passenger 6'))
            ->addBcc(new Recipient('passenger7@mailjet.com', 'Passenger 7'))
            ->setSubject('Your email flight plan!')
            ->setTextPart('Dear passenger, welcome to Mailjet! May the delivery force be with you!')
            ->setHtmlPart('<h3>Dear passenger, welcome to Mailjet!</h3><br />May the delivery force be with you!')
            ->setTemplateId(123456)
            ->setTemplateLanguage(false)
            ->setTemplateErrorReporting('flightcontrol@mailjet.com')
            ->setTemplateErrorDeliver('0');

        $attachment = new Attachment();

        $attachment->filename = 'image.jpg';
        $attachment->contentType = 'image/jpeg';
        $attachment->content = 'data:image/jpeg;base64,';

        $email
            ->addAttachment($attachment)
            ->addInlineAttachment($attachment)
            ->setPrio(2)
            ->setCampaign('Test')
            ->setDeduplicateCampaign(0)
            ->setTrackOpen(0)
            ->setCustomId('123456');

        $expected = [
            'FromEmail' => 'pilot@mailjet.com',
            'FromName' => 'Your Mailjet Pilot',
            'Sender' => false,
            'Recipients' => [
                [
                    'Email' => 'passenger@mailjet.com',
                    'Name' => 'Passenger 1',
                    'Vars' => '{"var1":"foo","var2":"bar"}'
                ]
            ],
            'To' => '"Passenger 2" <passenger2@mailjet.com>, "Passenger 3" <passenger3@mailjet.com>',
            'Cc' => '"Passenger 4" <passenger4@mailjet.com>, "Passenger 5" <passenger5@mailjet.com>',
            'Bcc' => '"Passenger 6" <passenger6@mailjet.com>, "Passenger 7" <passenger7@mailjet.com>',
            'Subject' => 'Your email flight plan!',
            'Text-part' => 'Dear passenger, welcome to Mailjet! May the delivery force be with you!',
            'Html-part' => '<h3>Dear passenger, welcome to Mailjet!</h3><br />May the delivery force be with you!',
            'Mj-TemplateID' => 123456,
            'Mj-TemplateLanguage' => false,
            'Mj-TemplateErrorReporting' => 'flightcontrol@mailjet.com',
            'Mj-TemplateErrorDeliver' => '0',
            'Attachments' => [
                [
                    'Filename' => 'image.jpg',
                    'Content-type' => 'image/jpeg',
                    'Content' => 'data:image/jpeg;base64,'
                ]
            ],
            'Inline_attachments' => [
                [
                    'Filename' => 'image.jpg',
                    'Content-type' => 'image/jpeg',
                    'Content' => 'data:image/jpeg;base64,'
                ]
            ],
            'Mj-prio' => 2,
            'Mj-campaign' => 'Test',
            'Mj-deduplicatecampaign' => 0,
            'Mj-trackopen' => 0,
            'Mj-CustomID' => '123456',
        ];

        $this->assertEquals($expected, $client->normalize($email));
    }

    /**
     * Tests, if default and empty values are EXCLUDED from the normalized data.
     *
     * @throws Exception
     * @throws ExceptionInterface
     */
    public function testPartialNormalization(): void
    {
        $client = $this->getClient();

        $email = new Email();

        $email->fromEmail = 'pilot@mailjet.com';
        $email->fromName = 'Your Mailjet Pilot';

        $email->addRecipient(new Recipient('passenger@mailjet.com', 'Passenger 1'));

        $email->subject = 'Your email flight plan!';
        $email->textPart = 'Dear passenger, welcome to Mailjet! May the delivery force be with you!';
        $email->htmlPart = '<h3>Dear passenger, welcome to Mailjet!</h3><br />May the delivery force be with you!';

        $expected = [
            'FromEmail' => "pilot@mailjet.com",
            'FromName' => "Your Mailjet Pilot",
            'Recipients' => [
                [
                    'Email' => "passenger@mailjet.com",
                    'Name' => "Passenger 1"
                ]
            ],
            'Subject' => "Your email flight plan!",
            'Text-part' => "Dear passenger, welcome to Mailjet! May the delivery force be with you!",
            'Html-part' => "<h3>Dear passenger, welcome to Mailjet!</h3><br />May the delivery force be with you!"
        ];

        $this->assertEquals($expected, $client->normalize($email));
    }

    /**
     * @throws Exception
     */
    public function testValidEmail(): void
    {
        $client = $this->getClient();
        $email = $this->getBasicEmail();
        $violations = $client->validate($email);

        $this->assertCount(0, $violations);
    }

    /**
     * @dataProvider getValidationData
     * @param list<string> $expectedMessages
     * @throws Exception
     */
    public function testValidation(
        Email $email,
        ?callable $preValidationTests,
        int $expectedViolations,
        array $expectedMessages
    ): void {
        $client = $this->getClient();

        if ($preValidationTests) {
            $preValidationTests($email);
        }

        $violations = $client->validate($email);

        $this->assertCount($expectedViolations, $violations);
        foreach ($violations as $violation) {
            $this->assertTrue(
                in_array($violation->getMessage(), $expectedMessages),
                sprintf('Message << %s >> not expected!', $violation->getMessage())
            );
        }
    }
}
