<?php

namespace Email\v3;

use ebitkov\Mailjet\Email\Recipient;
use ebitkov\Mailjet\Email\v3\Attachment;
use ebitkov\Mailjet\Email\v3\Email;
use ebitkov\Mailjet\Email\v3\SentEmail;
use ebitkov\Mailjet\Result;
use ebitkov\Mailjet\Tests\MailjetApiTestCase;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class EmailTest extends MailjetApiTestCase
{
    /**
     * @throws Exception
     */
    public function testSendEmail(): void
    {
        $client = $this->getClient();

        $email = new Email();

        $email->fromEmail = 'pilot@mailjet.com';
        $email->fromName = 'Your Mailjet Pilot';

        $email->addRecipient(new Recipient('passenger@mailjet.com', 'Passenger 1'));

        $email->subject = 'Your email flight plan!';
        $email->textPart = 'Dear passenger, welcome to Mailjet! May the delivery force be with you!';
        $email->htmlPart = '<h3>Dear passenger, welcome to Mailjet!</h3><br />May the delivery force be with you!';

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
     * This test ensures, that all arguments are properly normalized.
     *
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function testFullNormalization(): void
    {
        $client = $this->getClient();

        $email = new Email();

        $email->fromEmail = 'pilot@mailjet.com';
        $email->fromName = 'Your Mailjet Pilot';
        $email->sender = false;

        $email->addRecipient(
            new Recipient(
                'passenger@mailjet.com',
                'Passenger 1',
                [
                    'var1' => 'foo',
                    'var2' => 'bar'
                ]
            )
        );

        $email->addTo(new Recipient('passenger2@mailjet.com', 'Passenger 2'));
        $email->addTo(new Recipient('passenger3@mailjet.com', 'Passenger 3'));

        $email->addCc(new Recipient('passenger4@mailjet.com', 'Passenger 4'));
        $email->addCc(new Recipient('passenger5@mailjet.com', 'Passenger 5'));

        $email->addBcc(new Recipient('passenger6@mailjet.com', 'Passenger 6'));
        $email->addBcc(new Recipient('passenger7@mailjet.com', 'Passenger 7'));

        $email->subject = 'Your email flight plan!';
        $email->textPart = 'Dear passenger, welcome to Mailjet! May the delivery force be with you!';
        $email->htmlPart = '<h3>Dear passenger, welcome to Mailjet!</h3><br />May the delivery force be with you!';

        $email->templateId = 123456;
        $email->templateLanguage = false;
        $email->templateErrorReporting = 'flightcontrol@mailjet.com';
        $email->templateErrorDeliver = '0';

        $attachment = new Attachment();
        $attachment->filename = 'image.jpg';
        $attachment->contentType = 'image/jpeg';
        $attachment->content = 'data:image/jpeg;base64,';
        $email->addAttachment($attachment);
        $email->addInlineAttachment($attachment);

        $email->prio = 2;
        $email->campaign = 'Test';
        $email->deduplicateCampaign = 0;
        $email->trackOpen = 0;
        $email->customId = '123456';

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
}
