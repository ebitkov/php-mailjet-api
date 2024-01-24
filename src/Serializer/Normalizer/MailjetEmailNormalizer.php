<?php

namespace ebitkov\Mailjet\Serializer\Normalizer;

use ArrayObject;
use ebitkov\Mailjet\Email\v3\Message;
use ebitkov\Mailjet\Email\v3dot1\EmailList;
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

        $nameMap = [
            'TextPart' => 'Text-part',
            'HTMLPart' => 'Html-part',
            'InlineAttachments' => 'Inline_attachments',
            'Priority' => 'Mj-prio',
            'CustomCampaign' => 'Mj-campaign',
            'DeduplicateCampaign' => 'Mj-deduplicatecampaign',
            'TrackOpen' => 'Mj-trackopen',
            'Variables' => 'Vars',
            'TemplateID' => 'Mj-TemplateID',
            'TemplateLanguage' => 'Mj-TemplateLanguage',
            'TemplateErrorReporting' => 'Mj-TemplateErrorReporting',
            'TemplateErrorDeliver' => 'Mj-TemplateErrorDeliver',
            'CustomID' => 'Mj-CustomID'
        ];
        foreach ($nameMap as $needle => $replacement) {
            if (isset($data[$needle])) {
                $data[$replacement] = $data[$needle];
                unset($data[$needle]);
            }
        }

        // Attachments
        if (!empty($data['Attachments'])) {
            $data['Attachments'] = $this->remapAttachments($data['Attachments']);
        }
        if (!empty($data['Inline_attachments'])) {
            $data['Inline_attachments'] = $this->remapAttachments($data['Inline_attachments']);
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

    private function remapAttachments(array $attachments): array
    {
        $arr = [];
        foreach ($attachments as $attachment) {
            $arr[] = [
                'Filename' => $attachment['Filename'],
                'Content-type' => $attachment['ContentType'],
                'Content' => $attachment['Base64Content']
            ];
        }
        return $arr;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof Message
            && $format === 'send_api';
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Message::class => false
        ];
    }
}
