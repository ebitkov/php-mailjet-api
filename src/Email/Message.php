<?php

namespace ebitkov\Mailjet\Email;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * An email message.
 * Used in combination with Email.
 * @see https://dev.mailjet.com/email/reference/send-emails#v3_1_post_send
 */
final class Message
{
    #[Assert\NotBlank]
    private EmailAddress $from;

    private EmailAddress $sender;

    /**
     * @var EmailAddress[]
     */
    #[Assert\NotBlank]
    #[Assert\All(new Assert\Type(EmailAddress::class))]
    private array $to;

    /**
     * @var EmailAddress[]
     */
    #[Assert\All([
        new Assert\Type(EmailAddress::class)
    ])]
    private array $cc = [];

    /**
     * @var EmailAddress[]
     */
    #[Assert\All([
        new Assert\Type(EmailAddress::class)
    ])]
    private array $bcc = [];

    private EmailAddress $replyTo;

    private string $subject;

    private string $textPart;

    private string $htmlPart;

    private int $templateId;

    private bool $templateLanguage;

    private EmailAddress $templateErrorReporting;

    private bool $templateErrorDeliver;

    /**
     * @var Attachment[]
     */
    #[Assert\All([
        new Assert\Type(Attachment::class)
    ])]
    private array $attachments = [];

    /**
     * @var InlineAttachment[]
     */
    #[Assert\All([
        new Assert\Type(InlineAttachment::class)
    ])]
    private array $inlineAttachments = [];

    private int $priority;

    private string $customCampaign;

    private bool $deduplicateCampaign;

    #[Assert\Choice(['account_default', 'disabled', 'enabled'])]
    private string $trackOpens;

    #[Assert\Choice(['account_default', 'disabled', 'enabled'])]
    private string $trackClicks;

    private string $customId;

    private string $eventPayload;

    private string $urlTags;

    /**
     * @var array<string, string>
     */
    private array $headers;

    /**
     * @var array<string, string>
     */
    private array $variables;

    public function getFrom(): EmailAddress
    {
        return $this->from;
    }

    public function setFrom(EmailAddress $from): self
    {
        $this->from = $from;
        return $this;
    }

    public function getSender(): EmailAddress
    {
        return $this->sender;
    }

    public function setSender(EmailAddress $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @return EmailAddress[]
     */
    public function getTo(): array
    {
        return $this->to;
    }

    /**
     * @param EmailAddress[] $to
     * @return $this
     */
    public function setTo(array $to): self
    {
        $this->to = $to;
        return $this;
    }

    public function addTo(EmailAddress $to): self
    {
        $this->to[] = $to;
        return $this;
    }

    /**
     * @return EmailAddress[]
     */
    public function getCc(): array
    {
        return $this->cc;
    }

    /**
     * @param EmailAddress[] $cc
     */
    public function setCc(array $cc): self
    {
        $this->cc = $cc;
        return $this;
    }

    public function addCc(EmailAddress $cc): self
    {
        $this->cc[] = $cc;
        return $this;
    }

    /**
     * @return EmailAddress[]
     */
    public function getBcc(): array
    {
        return $this->bcc;
    }

    /**
     * @param EmailAddress[] $bcc
     */
    public function setBcc(array $bcc): self
    {
        $this->bcc = $bcc;
        return $this;
    }

    public function addBcc(EmailAddress $bcc): self
    {
        $this->bcc[] = $bcc;
        return $this;
    }

    public function getReplyTo(): EmailAddress
    {
        return $this->replyTo;
    }

    public function setReplyTo(EmailAddress $replyTo): self
    {
        $this->replyTo = $replyTo;
        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function getTextPart(): string
    {
        return $this->textPart;
    }

    public function setTextPart(string $textPart): self
    {
        $this->textPart = $textPart;
        return $this;
    }

    public function getHtmlPart(): string
    {
        return $this->htmlPart;
    }

    public function setHtmlPart(string $htmlPart): self
    {
        $this->htmlPart = $htmlPart;
        return $this;
    }

    public function getTemplateId(): int
    {
        return $this->templateId;
    }

    public function setTemplateId(int $templateId): self
    {
        $this->templateId = $templateId;
        return $this;
    }

    public function isTemplateLanguage(): bool
    {
        return $this->templateLanguage;
    }

    public function setTemplateLanguage(bool $templateLanguage): self
    {
        $this->templateLanguage = $templateLanguage;
        return $this;
    }

    public function getTemplateErrorReporting(): EmailAddress
    {
        return $this->templateErrorReporting;
    }

    public function setTemplateErrorReporting(EmailAddress $templateErrorReporting): self
    {
        $this->templateErrorReporting = $templateErrorReporting;
        return $this;
    }

    public function isTemplateErrorDeliver(): bool
    {
        return $this->templateErrorDeliver;
    }

    public function setTemplateErrorDeliver(bool $templateErrorDeliver): self
    {
        $this->templateErrorDeliver = $templateErrorDeliver;
        return $this;
    }

    /**
     * @return Attachment[]
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * @param Attachment[] $attachments
     */
    public function setAttachments(array $attachments): self
    {
        $this->attachments = $attachments;
        return $this;
    }

    public function addAttachment(Attachment $attachment): self
    {
        $this->attachments[] = $attachment;
        return $this;
    }

    /**
     * @return InlineAttachment[]
     */
    public function getInlineAttachments(): array
    {
        return $this->inlineAttachments;
    }

    /**
     * @param InlineAttachment[] $inlineAttachments
     */
    public function setInlineAttachments(array $inlineAttachments): self
    {
        $this->inlineAttachments = $inlineAttachments;
        return $this;
    }

    public function addInlineAttachment(InlineAttachment $attachment): self
    {
        $this->inlineAttachments[] = $attachment;
        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function getCustomCampaign(): string
    {
        return $this->customCampaign;
    }

    public function setCustomCampaign(string $customCampaign): self
    {
        $this->customCampaign = $customCampaign;
        return $this;
    }

    public function isDeduplicateCampaign(): bool
    {
        return $this->deduplicateCampaign;
    }

    public function setDeduplicateCampaign(bool $deduplicateCampaign): self
    {
        $this->deduplicateCampaign = $deduplicateCampaign;
        return $this;
    }

    public function getTrackOpens(): string
    {
        return $this->trackOpens;
    }

    public function setTrackOpens(string $trackOpens): self
    {
        $this->trackOpens = $trackOpens;
        return $this;
    }

    public function getTrackClicks(): string
    {
        return $this->trackClicks;
    }

    public function setTrackClicks(string $trackClicks): self
    {
        $this->trackClicks = $trackClicks;
        return $this;
    }

    public function getCustomId(): string
    {
        return $this->customId;
    }

    public function setCustomId(string $customId): self
    {
        $this->customId = $customId;
        return $this;
    }

    public function getEventPayload(): string
    {
        return $this->eventPayload;
    }

    public function setEventPayload(string $eventPayload): self
    {
        $this->eventPayload = $eventPayload;
        return $this;
    }

    public function getUrlTags(): string
    {
        return $this->urlTags;
    }

    public function setUrlTags(string $urlTags): self
    {
        $this->urlTags = $urlTags;
        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array<string, string> $headers
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param array<string, string> $variables
     */
    public function setVariables(array $variables): self
    {
        $this->variables = $variables;
        return $this;
    }
}
