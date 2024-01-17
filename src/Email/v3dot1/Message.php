<?php

namespace ebitkov\Mailjet\Email\v3dot1;

use ebitkov\Mailjet\Email\Contact;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An email message.
 * Used in combination with Email.
 */
final class Message
{
    #[Assert\NotBlank]
    public Contact $from;

    public Contact $sender;

    #[Assert\NotBlank]
    public Contact $to;

    public array $cc;

    public array $bcc;

    public Contact $replyTo;

    public string $subject;

    public string $textPart;

    public string $htmlPart;

    public int $templateId;

    public bool $templateLanguage;

    public Contact $templateErrorReporting;

    public bool $templateErrorDeliver;

    /**
     * @var list<Attachment>
     */
    #[Assert\All([
        new Assert\Type(Attachment::class)
    ])]
    public array $attachments;

    /**
     * @var list<InlineAttachment>
     */
    #[Assert\All([
        new Assert\Type(InlineAttachment::class)
    ])]
    public array $inlineAttachments;

    public int $priority;

    public string $customCampaign;

    public bool $deduplicateCampaign;

    #[Assert\Choice(['account_default', 'disabled', 'enabled'])]
    public string $trackOpens;

    #[Assert\Choice(['account_default', 'disabled', 'enabled'])]
    public string $trackClicks;

    public string $customId;

    public string $eventPayload;

    public string $urlTags;

    public array $headers;

    public array $variables;
}
