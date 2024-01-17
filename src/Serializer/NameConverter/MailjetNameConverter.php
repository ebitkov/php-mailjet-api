<?php

namespace ebitkov\Mailjet\Serializer\NameConverter;

use ebitkov\Mailjet\Email\v3\Attachment;
use ebitkov\Mailjet\Email\v3\Email;
use Symfony\Component\Serializer\NameConverter\AdvancedNameConverterInterface;

class MailjetNameConverter implements AdvancedNameConverterInterface
{
    /**
     * @param array<string, bool> $context
     */
    public function normalize(
        string $propertyName,
        string $class = null,
        string $format = null,
        array $context = []
    ): string {
        $default = new UpperCamelCaseToLowerCamelCaseNameConverter();

        // custom rules for Send API v3 email
        if ($class === Email::class) {
            return match ($propertyName) {
                'textPart' => 'Text-part',
                'htmlPart' => 'Html-part',
                'templateId' => 'Mj-TemplateID',
                'templateLanguage' => 'Mj-TemplateLanguage',
                'templateErrorReporting' => 'Mj-TemplateErrorReporting',
                'templateErrorDeliver' => 'Mj-TemplateErrorDeliver',
                'inlineAttachments' => 'Inline_attachments',
                'prio' => 'Mj-prio',
                'campaign' => 'Mj-campaign',
                'deduplicateCampaign' => 'Mj-deduplicatecampaign',
                'trackOpen' => 'Mj-trackopen',
                'customId' => 'Mj-CustomID',
                'eventPayload' => 'Mj-EventPayload',
                default => $default->normalize($propertyName)
            };
        }

        if ($class === Attachment::class) {
            return match ($propertyName) {
                'contentType' => 'Content-type',
                default => $default->normalize($propertyName)
            };
        }

        return $default->normalize($propertyName);
    }

    /**
     * @param array<string, bool> $context
     */
    public function denormalize(
        string $propertyName,
        string $class = null,
        string $format = null,
        array $context = []
    ): string {
        return (new UpperCamelCaseToLowerCamelCaseNameConverter())->denormalize($propertyName);
    }
}
