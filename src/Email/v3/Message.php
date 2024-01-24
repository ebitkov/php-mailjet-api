<?php

namespace ebitkov\Mailjet\Email\v3;

use ebitkov\Mailjet\Email\Attachment;
use ebitkov\Mailjet\Email\EmailAddress;

final class Message
{
    private ?string $fromEmail;

    private ?string $fromName;

    private ?bool $sender;

    /**
     * @var Recipient[]
     */
    private array $recipients;

    /**
     * @var EmailAddress[]
     */
    private ?array $to;

    /**
     * @var EmailAddress[]
     */
    private ?array $cc;

    /**
     * @var EmailAddress[]
     */
    private ?array $bcc;

    private ?string $subject;

    private ?string $textPart;

    private ?string $htmlPart;

    private ?int $templateId;

    private ?bool $templateLanguage;

    private ?EmailAddress $templateErrorReporting;

    private ?bool $templateErrorDeliver;

    /**
     * @var Attachment[]
     */
    private ?array $attachments;

    /**
     * @var Attachment[]
     */
    private ?array $inlineAttachments;

    private ?int $priority;

    private ?string $customCampaign;

    private ?int $deduplicateCampaign;

    private ?int $trackOpen;

    private ?string $customId;

    private ?string $eventPayload;

    /**
     * @var array<string, string>
     */
    private ?array $headers;

    /**
     * @var array<string, string>
     */
    private ?array $variables;


    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(?string $fromEmail): self
    {
        $this->fromEmail = $fromEmail;
        return $this;
    }

    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    public function setFromName(?string $fromName): self
    {
        $this->fromName = $fromName;
        return $this;
    }

    public function getSender(): ?bool
    {
        return $this->sender;
    }

    public function setSender(?bool $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function setRecipients(array $recipients): self
    {
        $this->recipients = $recipients;
        return $this;
    }

    public function getTo(): string
    {
        return implode(', ', $this->to);
    }

    public function setTo(?array $to): self
    {
        $this->to = $to;
        return $this;
    }

    public function addTo(EmailAddress $to): self
    {
        $this->to[] = $to;
        return $this;
    }

    public function getCc(): string
    {
        return implode(', ', $this->cc);
    }

    public function setCc(?array $cc): self
    {
        $this->cc = $cc;
        return $this;
    }

    public function addCc(EmailAddress $cc): self
    {
        $this->cc[] = $cc;
        return $this;
    }

    public function getBcc(): string
    {
        return implode(', ', $this->bcc);
    }

    public function setBcc(?array $bcc): self
    {
        $this->bcc = $bcc;
        return $this;
    }

    public function addBcc(EmailAddress $bcc): self
    {
        $this->bcc[] = $bcc;
        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function getTextPart(): ?string
    {
        return $this->textPart;
    }

    public function setTextPart(?string $textPart): self
    {
        $this->textPart = $textPart;
        return $this;
    }

    public function getHtmlPart(): ?string
    {
        return $this->htmlPart;
    }

    public function setHtmlPart(?string $htmlPart): self
    {
        $this->htmlPart = $htmlPart;
        return $this;
    }

    public function getTemplateId(): ?int
    {
        return $this->templateId;
    }

    public function setTemplateId(?int $templateId): self
    {
        $this->templateId = $templateId;
        return $this;
    }

    public function getTemplateLanguage(): ?bool
    {
        return $this->templateLanguage;
    }

    public function setTemplateLanguage(?bool $templateLanguage): self
    {
        $this->templateLanguage = $templateLanguage;
        return $this;
    }

    public function getTemplateErrorReporting(): ?string
    {
        return $this->templateErrorReporting;
    }

    public function setTemplateErrorReporting(?EmailAddress $templateErrorReporting): self
    {
        $this->templateErrorReporting = $templateErrorReporting;
        return $this;
    }

    public function getTemplateErrorDeliver(): string
    {
        return $this->templateErrorDeliver ? 'deliver' : '0';
    }

    public function setTemplateErrorDeliver(?bool $templateErrorDeliver): self
    {
        $this->templateErrorDeliver = $templateErrorDeliver;
        return $this;
    }

    /**
     * @return ?Attachment[]
     */
    public function getAttachments(): ?array
    {
        return $this->attachments;
    }

    /**
     * @param Attachment[]|null $attachments
     */
    public function setAttachments(?array $attachments): self
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
     * @return Attachment[]|null
     */
    public function getInlineAttachments(): ?array
    {
        return $this->inlineAttachments;
    }

    /**
     * @param Attachment[]|null $inlineAttachments
     */
    public function setInlineAttachments(?array $inlineAttachments): self
    {
        $this->inlineAttachments = $inlineAttachments;
        return $this;
    }

    public function addInlineAttachment(Attachment $attachment): self
    {
        $this->inlineAttachments[] = $attachment;
        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function getCustomCampaign(): ?string
    {
        return $this->customCampaign;
    }

    public function setCustomCampaign(?string $customCampaign): self
    {
        $this->customCampaign = $customCampaign;
        return $this;
    }

    public function getDeduplicateCampaign(): ?int
    {
        return $this->deduplicateCampaign;
    }

    public function setDeduplicateCampaign(?int $deduplicateCampaign): self
    {
        $this->deduplicateCampaign = $deduplicateCampaign;
        return $this;
    }

    public function getTrackOpen(): ?int
    {
        return $this->trackOpen;
    }

    public function setTrackOpen(?int $trackOpen): self
    {
        $this->trackOpen = $trackOpen;
        return $this;
    }

    public function getCustomId(): ?string
    {
        return $this->customId;
    }

    public function setCustomId(?string $customId): self
    {
        $this->customId = $customId;
        return $this;
    }

    public function getEventPayload(): ?string
    {
        return $this->eventPayload;
    }

    public function setEventPayload(?string $eventPayload): self
    {
        $this->eventPayload = $eventPayload;
        return $this;
    }

    /**
     * @return ?array<string, string>
     */
    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    /**
     * @param array<string, string>|null $headers
     */
    public function setHeaders(?array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return array<string, string>|null
     */
    public function getVariables(): ?array
    {
        return $this->variables;
    }

    /**
     * @param array<string, string> $variables
     */
    public function setVariables(?array $variables): self
    {
        $this->variables = $variables;
        return $this;
    }
}
