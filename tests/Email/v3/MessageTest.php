<?php

namespace ebitkov\Mailjet\Tests\Email\v3;

use ebitkov\Mailjet\Email\Attachment;
use ebitkov\Mailjet\Email\EmailAddress;
use ebitkov\Mailjet\Email\v3\Message;
use ebitkov\Mailjet\Email\v3\SentEmail;
use ebitkov\Mailjet\Result;
use ebitkov\Mailjet\Tests\MailjetApiTestCase;

class MessageTest extends MailjetApiTestCase
{
    public function testFullNormalization(): void
    {
        $client = $this->getClient();

        $message = new Message();

        $message
            ->setFromEmail('pilot@mailjet.com')
            ->setFromName('Your Mailjet Pilot')
            ->setSender(true)
            ->addTo(new EmailAddress('passenger@mailjet.com', 'Passenger 1'))
            ->addCc(new EmailAddress('passenger4@mailjet.com', 'Passenger 4'))
            ->addCc(new EmailAddress('passenger5@mailjet.com', 'Passenger 5'))
            ->addBcc(new EmailAddress('passenger6@mailjet.com', 'Passenger 6'))
            ->addBcc(new EmailAddress('passenger7@mailjet.com', 'Passenger 7'))
            ->setSubject('Your email flight plan!')
            ->setTextPart('Dear passenger, welcome to Mailjet! May the delivery force be with you!')
            ->setHtmlPart('<h3>Dear passenger, welcome to Mailjet!</h3><br />May the delivery force be with you!')
            ->setTemplateId(123456)
            ->setTemplateLanguage(false)
            ->setTemplateErrorReporting(new EmailAddress('flightcontrol@mailjet.com'))
            ->setTemplateErrorDeliver(false)
            ->setPriority(2)
            ->setCustomCampaign('Test')
            ->setDeduplicateCampaign(false)
            ->setTrackOpen(0)
            ->setCustomId('123456')
            ->setVariables([
                'foo' => 'bar'
            ]);

        $attachment = new Attachment();
        $attachment->filename = 'image.jpg';
        $attachment->contentType = 'image/jpeg';
        $attachment->base64Content = 'data:image/jpeg;base64,';

        $message
            ->addAttachment($attachment)
            ->addInlineAttachment($attachment);

        $expected = [
            'FromEmail' => 'pilot@mailjet.com',
            'FromName' => 'Your Mailjet Pilot',
            'Sender' => true,
            'To' => '"Passenger 1" <passenger@mailjet.com>',
            'Cc' => '"Passenger 4" <passenger4@mailjet.com>, "Passenger 5" <passenger5@mailjet.com>',
            'Bcc' => '"Passenger 6" <passenger6@mailjet.com>, "Passenger 7" <passenger7@mailjet.com>',
            'Subject' => 'Your email flight plan!',
            'Text-part' => 'Dear passenger, welcome to Mailjet! May the delivery force be with you!',
            'Html-part' => '<h3>Dear passenger, welcome to Mailjet!</h3><br />May the delivery force be with you!',
            'Mj-TemplateID' => 123456,
            'Mj-TemplateLanguage' => false,
            'Mj-TemplateErrorReporting' => '<flightcontrol@mailjet.com>',
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
            'Vars' => [
                'foo' => 'bar'
            ]
        ];

        $this->assertEquals(
            $expected,
            $client->normalize($message, 'send_api')
        );
    }

    public function testSend(): void
    {
        $client = $this->getClient();

        $message = new Message();

        $message
            ->setFromEmail('pilot@mailjet.com')
            ->setFromName('Your Mailjet Pilot')
            ->addTo(new EmailAddress('passenger@mailjet.com', 'Passenger 1'))
            ->setSubject('Your email flight plan!')
            ->setTextPart('Dear passenger, welcome to Mailjet! May the delivery force be with you!')
            ->setHtmlPart('<h3>Dear passenger, welcome to Mailjet!</h3><br />May the delivery force be with you!');

        $result = $client->send($message);

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
}
