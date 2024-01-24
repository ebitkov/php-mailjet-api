<?php

namespace ebitkov\Mailjet\Serializer\Normalizer;

use ArrayObject;
use ebitkov\Mailjet\Email\EmailList;
use Exception;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class MailjetEmailNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;


    /**
     * @param EmailList $object
     * @param string|null $format
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>|string|int|float|bool|ArrayObject<string, mixed>|null
     *
     * @throws ExceptionInterface
     */
    public function normalize(
        mixed $object,
        string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|null {
        $data = $this->removeEmptyData(
            $this->normalizer->normalize(
                $object,
                null,
                [
                    AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                    AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => true,
                ]
            )
        );

        if ('v3' == $context['mj_api_version']) {
            // special transformation for Send API v3
            if (count($data['Messages']) > 1) {
                /*
                 * todo: support multiple messages with v3
                 * This is actually not quite right.
                 * The API only supports one message at multiple addresses,
                 * while the v3.1 supports also different message contents.
                 *
                 * A possible workaround is to rely on the "Globals" attribute and notify the
                 * user to use that instead.
                 */
                throw new Exception('The Send API v3 only supports one message at a time.');
            }

            $data = [
                ...$data['Messages'][0]
            ];
            $message = $object->messages[0];

            // unset unsupported parameters
            unset($data['ReplyTo']);
            unset($data['TrackClicks']);

            // From & Sender
            if (!empty($data['Sender'])) {
                $sender = $data['Sender'];
                $data['Sender'] = true;
                $data['FromEmail'] = $sender['Email'];
                if (isset($sender['Name'])) {
                    $data['FromName'] = $sender['Name'];
                }
                unset($data['From']);
            } else {
                $data['FromEmail'] = $data['From']['Email'];
                if (isset($data['From']['Name'])) {
                    $data['FromName'] = $data['From']['Name'];
                }
                unset($data['From']);
            }

            // To
            $data['To'] = implode(', ', $message->getTo());

            // CC
            if (isset($data['Cc'])) {
                $data['Cc'] = implode(', ', $message->getCc());
            }

            // BCC
            if (isset($data['Bcc'])) {
                $data['Bcc'] = implode(', ', $message->getBcc());
            }

            // rename parameters
            $map = [
                'TextPart' => 'Text-part',
                'HTMLPart' => 'Html-part',
                'InlineAttachments' => 'Inline_attachments',
                'TemplateID' => 'Mj-TemplateID',
                'TemplateLanguage' => 'Mj-TemplateLanguage',
                'Priority' => 'Mj-prio',
                'CustomCampaign' => 'Mj-campaign',
                'CustomID' => 'Mj-CustomID',
                'Variables' => 'Vars'
            ];

            foreach ($map as $k => $v) {
                if (isset($data[$k])) {
                    $data[$v] = $data[$k];
                    unset($data[$k]);
                }
            }

            // Attachments
            if (isset($data['Attachments'])) {
                $attachments = [];
                foreach ($data['Attachments'] as $attachment) {
                    $attachment['Content-type'] = $attachment['ContentType'];
                    $attachment['Content'] = $attachment['Base64Content'];

                    unset($attachment['ContentType']);
                    unset($attachment['Base64Content']);

                    $attachments[] = $attachment;
                }
                $data['Attachments'] = $attachments;
            }
            if (isset($data['Inline_attachments'])) {
                $attachments = [];
                foreach ($data['Inline_attachments'] as $attachment) {
                    $attachment['Content-type'] = $attachment['ContentType'];
                    $attachment['Content'] = $attachment['Base64Content'];

                    unset($attachment['ContentType']);
                    unset($attachment['Base64Content']);
                    unset($attachment['ContentID']);

                    $attachments[] = $attachment;
                }
                $data['Inline_attachments'] = $attachments;
            }

            // TemplateErrorReporting
            if (!empty($data['TemplateErrorReporting'])) {
                $data['Mj-TemplateErrorReporting'] = $message->getTemplateErrorReporting()->email;
                unset($data['TemplateErrorReporting']);
            }

            // TemplateErrorDeliver
            if (isset($data['TemplateErrorDeliver'])) {
                $data['Mj-TemplateErrorDeliver'] = $data['TemplateErrorDeliver'] ? 'deliver' : '0';
                unset($data['TemplateErrorDeliver']);
            }

            // DeduplicateCampaign
            if (isset($data['DeduplicateCampaign'])) {
                $data['Mj-deduplicatecampaign'] = (int)$data['DeduplicateCampaign'];
                unset($data['DeduplicateCampaign']);
            }

            // TrackOpens
            if (isset($data['TrackOpens'])) {
                /** @var "account_default"|"disabled"|"enabled" $trackOpens */
                $trackOpens = $data['TrackOpens'];
                $data['Mj-trackopen'] = match ($trackOpens) {
                    'account_default' => 0,
                    'disabled' => 1,
                    'enabled' => 2
                };
                unset($data['TrackOpens']);
            }

            $data = array_filter($data, function ($value) {
                return $value !== null
                    and $value !== []
                    and $value !== "";
            });
        }

        return $data;
    }

    public function removeEmptyData(array $data): array
    {
        foreach ($data as $k => $v) {
            if (
                $v === null ||
                $v === [] ||
                $v === ''
            ) {
                unset($data[$k]);
            } else {
                if (is_array($v)) {
                    $data[$k] = $this->removeEmptyData($v);
                }
            }
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof EmailList
            && $format === 'send_api';
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            EmailList::class => false
        ];
    }
}
