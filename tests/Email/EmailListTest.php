<?php

namespace Email;

use ebitkov\Mailjet\Email\Attachment;
use ebitkov\Mailjet\Email\EmailAddress;
use ebitkov\Mailjet\Email\EmailList;
use ebitkov\Mailjet\Email\InlineAttachment;
use ebitkov\Mailjet\Email\Message;
use ebitkov\Mailjet\Email\v3\SentEmail;
use ebitkov\Mailjet\Result;
use ebitkov\Mailjet\Tests\MailjetApiTestCase;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class EmailListTest extends MailjetApiTestCase
{
    public function testSendV3Email(): void
    {
        $client = $this->getClient('v3');
        $email = $this->getBasicEmailList();

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

    private static function getBasicEmailList(): EmailList
    {
        $message = (new Message())
            ->setFrom(new EmailAddress('pilot@mailjet.com', 'Your Mailjet Pilot'))
            ->setTo(new EmailAddress('passenger@mailjet.com', 'Passenger 1'))
            ->setSubject('Your email flight plan!')
            ->setTextPart('Dear passenger, welcome to Mailjet! May the delivery force be with you!')
            ->setHtmlPart('<h3>Dear passenger, welcome to Mailjet!</h3><br />May the delivery force be with you!');

        return (new EmailList())
            ->addMessage($message);
    }

    /**
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function testNormalizeV3(): void
    {
        $client = $this->getClient('v3');
        $emailList = $this->getFullEmailList();

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

        $this->assertEquals($expected, $client->normalize($emailList, 'send_api'));
    }

    private function getFullEmailList(): EmailList
    {
        $email = new EmailList();
        $message = new Message();

        $email->sandboxMode = false;
        $email->advanceErrorHandling = false;

        # todo $email->globals

        $message
            ->setFrom(new EmailAddress('pilot@mailjet.com', 'Your Mailjet Pilot'))
            ->setSender(new EmailAddress('pilot@mailjet.com', 'Your Mailjet Pilot'))
            ->setTo(new EmailAddress('passenger@mailjet.com', 'Passenger 1'))
            ->addCc(new EmailAddress('passenger4@mailjet.com', 'Passenger 4'))
            ->addCc(new EmailAddress('passenger5@mailjet.com', 'Passenger 5'))
            ->addBcc(new EmailAddress('passenger6@mailjet.com', 'Passenger 6'))
            ->addBcc(new EmailAddress('passenger7@mailjet.com', 'Passenger 7'))
            ->setReplyTo(new EmailAddress('oneway@mailjet.com'))
            ->setSubject('Your email flight plan!')
            ->setTextPart('Dear passenger, welcome to Mailjet! May the delivery force be with you!')
            ->setHtmlPart('<h3>Dear passenger, welcome to Mailjet!</h3><br />May the delivery force be with you!')
            ->setTemplateId(123456)
            ->setTemplateLanguage(false)
            ->setTemplateErrorReporting(new EmailAddress('flightcontrol@mailjet.com'))
            ->setTemplateErrorDeliver(false);

        $attachment = new Attachment();

        $attachment->filename = 'image.jpg';
        $attachment->contentType = 'image/jpeg';
        $attachment->base64Content = 'data:image/jpeg;base64,';

        $message->addAttachment($attachment);

        $attachment = new InlineAttachment();
        $attachment->filename = 'image.jpg';
        $attachment->contentType = 'image/jpeg';
        $attachment->base64Content = 'data:image/jpeg;base64,';
        $attachment->contentId = 'attachment';

        $message->addInlineAttachment($attachment)
            ->setPriority(2)
            ->setCustomCampaign('Test')
            ->setDeduplicateCampaign(false)
            ->setTrackOpens('account_default')
            ->setTrackClicks('account_default')
            ->setCustomId('123456');

        $email->addMessage($message);

        return $email;
    }

    public function testNormalizeV3dot1(): void
    {
        $client = $this->getClient();
        $this->assertSame('v3.1', $client->getApiVersion());

        $emailList = $this->getFullEmailList();

        $expected = [
            'SandboxMode' => false,
            'AdvanceErrorHandling' => false,
            'Messages' => [
                [
                    'From' => [
                        'Email' => 'pilot@mailjet.com',
                        'Name' => 'Your Mailjet Pilot'
                    ],
                    'Sender' => [
                        'Email' => 'pilot@mailjet.com',
                        'Name' => 'Your Mailjet Pilot'
                    ],
                    'To' => [
                        'Email' => 'passenger@mailjet.com',
                        'Name' => 'Passenger 1'
                    ],
                    'Cc' => [
                        [
                            'Email' => 'passenger4@mailjet.com',
                            'Name' => 'Passenger 4'
                        ],
                        [
                            'Email' => 'passenger5@mailjet.com',
                            'Name' => 'Passenger 5'
                        ]
                    ],
                    'Bcc' => [
                        [
                            'Email' => 'passenger6@mailjet.com',
                            'Name' => 'Passenger 6'
                        ],
                        [
                            'Email' => 'passenger7@mailjet.com',
                            'Name' => 'Passenger 7'
                        ]
                    ],
                    'ReplyTo' => [
                        'Email' => 'oneway@mailjet.com'
                    ],
                    'Subject' => 'Your email flight plan!',
                    'TextPart' => 'Dear passenger, welcome to Mailjet! May the delivery force be with you!',
                    'HTMLPart' =>
                        '<h3>Dear passenger, welcome to Mailjet!</h3><br />' .
                        'May the delivery force be with you!',
                    'TemplateID' => 123456,
                    'TemplateLanguage' => false,
                    'TemplateErrorReporting' => [
                        'Email' => 'flightcontrol@mailjet.com'
                    ],
                    'TemplateErrorDeliver' => false,
                    'Attachments' => [
                        [
                            'Filename' => 'image.jpg',
                            'ContentType' => 'image/jpeg',
                            'Base64Content' => 'data:image/jpeg;base64,'
                        ]
                    ],
                    'InlineAttachments' => [
                        [
                            'Filename' => 'image.jpg',
                            'ContentType' => 'image/jpeg',
                            'Base64Content' => 'data:image/jpeg;base64,',
                            'ContentID' => 'attachment'
                        ]
                    ],
                    'Priority' => 2,
                    'CustomCampaign' => 'Test',
                    'DeduplicateCampaign' => false,
                    'TrackOpens' => 'account_default',
                    'TrackClicks' => 'account_default',
                    'CustomID' => '123456',
                ]
            ]
        ];

        $this->assertEquals($expected, $client->normalize($emailList, 'send_api'));
    }
}
