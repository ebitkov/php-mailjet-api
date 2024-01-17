<?php

namespace ebitkov\Mailjet\Email\v3;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * An email message for the Send API v3.
 *
 * @see https://dev.mailjet.com/email/reference/send-emails#v3_post_send
 */
final class Email
{
    #[Assert\Email]
    public string $fromEmail;

    public string $fromName;

    public bool $sender;

    /**
     * @var list<Recipient>
     */
    #[Assert\All([
        new Assert\Type(Recipient::class)
    ])]
    #[Assert\Expression(
        'this.getTo() or this.recipients',
        message: '"Recipients" or "To" is required.'
    )]
    public ?array $recipients = null;

    public string $subject;

    #[Assert\Expression(
        'this.textPart or this.htmlPart',
        message: '"Text-part" or "Html-part" is required.'
    )]
    public ?string $textPart = null;

    #[Assert\Expression(
        'this.htmlPart or this.textPart',
        message: '"Text-part" or "Html-part" is required.'
    )]
    public ?string $htmlPart = null;

    public int $templateId;

    public bool $templateLanguage;

    #[Assert\Email]
    public string $templateErrorReporting;

    /**
     * @var "deliver"|"0"
     */
    #[Assert\Choice(
        ["deliver", "0"],
        message: 'The value for "Mj-TemplateErrorDeliver" is not valid. Allowed are [ "deliver", "0" ].'
    )]
    public string $templateErrorDeliver;

    /**
     * @var list<Attachment>
     */
    #[Assert\All([
        new Assert\Type(Attachment::class)
    ])]
    public array $attachments;

    /**
     * @var list<Attachment>
     */
    #[Assert\All([
        new Assert\Type(Attachment::class)
    ])]
    public array $inlineAttachments;

    public int $prio;

    public string $campaign;

    /**
     * @var int<0, 1>
     */
    #[Assert\Choice([0, 1])]
    public int $deduplicateCampaign;

    /**
     * @var int<0, 2>
     */
    #[Assert\Choice([0, 1, 2])]
    public int $trackOpen;

    public string $customId;

    public string $eventPayload;

    /**
     * @var array<string, string>
     */
    public array $headers;

    /**
     * @var array<string, string>
     */
    public array $vars;

    /**
     * @var list<Recipient>
     */
    private array $to = [];

    /**
     * @var list<Recipient>
     */
    #[Assert\Expression(
        "!this.getCc() or !this.recipients",
        message: '"Cc" can not be used together with "Recipients". Use "To" instead.'
    )]
    private array $cc = [];

    /**
     * @var list<Recipient>
     */
    #[Assert\Expression(
        "!this.getBcc() or !this.recipients",
        message: '"Bcc" can not be used together with "Recipients". Use "To" instead.'
    )]
    private array $bcc = [];

    public function addTo(Recipient $recipient): self
    {
        $this->to[] = $recipient;
        return $this;
    }

    public function addCc(Recipient $recipient): self
    {
        $this->cc[] = $recipient;
        return $this;
    }

    public function addBcc(Recipient $recipient): self
    {
        $this->bcc[] = $recipient;
        return $this;
    }

    public function getTo(): ?string
    {
        if ($this->to) {
            return implode(', ', $this->to);
        }

        return null;
    }

    public function getCc(): ?string
    {
        if ($this->cc) {
            return implode(', ', $this->cc);
        }

        return null;
    }

    public function getBcc(): ?string
    {
        if ($this->bcc) {
            return implode(', ', $this->bcc);
        }

        return null;
    }

    public function addAttachment(Attachment $attachment): self
    {
        $this->attachments[] = $attachment;
        return $this;
    }

    public function addInlineAttachment(Attachment $attachment): self
    {
        $this->inlineAttachments[] = $attachment;
        return $this;
    }

    /**
     * @param list<Recipient> $recipients
     */
    public function setRecipients(array $recipients): self
    {
        foreach ($this->recipients as $recipient) {
            $this->addRecipient($recipient);
        }

        return $this;
    }

    public function addRecipient(Recipient $recipient): self
    {
        $this->recipients[] = $recipient;
        return $this;
    }

    public function clearRecipients(): self
    {
        $this->recipients = [];
        return $this;
    }

    public function setTemplateErrorDeliver(string $templateErrorDeliver): self
    {
        $this->templateErrorDeliver = $templateErrorDeliver;
        return $this;
    }

    public function getFromEmail(): string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(string $fromEmail): self
    {
        $this->fromEmail = $fromEmail;
        return $this;
    }

    public function getFromName(): string
    {
        return $this->fromName;
    }

    public function setFromName(string $fromName): self
    {
        $this->fromName = $fromName;
        return $this;
    }

    public function isSender(): bool
    {
        return $this->sender;
    }

    public function setSender(bool $sender): self
    {
        $this->sender = $sender;
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

    public function getTextPart(): ?string
    {
        return $this->textPart;
    }

    public function setTextPart(string $textPart): self
    {
        $this->textPart = $textPart;
        return $this;
    }

    public function getHtmlPart(): ?string
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

    public function getTemplateErrorReporting(): string
    {
        return $this->templateErrorReporting;
    }

    public function setTemplateErrorReporting(string $templateErrorReporting): self
    {
        $this->templateErrorReporting = $templateErrorReporting;
        return $this;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function setAttachments(array $attachments): self
    {
        $this->attachments = $attachments;
        return $this;
    }

    public function getInlineAttachments(): array
    {
        return $this->inlineAttachments;
    }

    public function setInlineAttachments(array $inlineAttachments): self
    {
        $this->inlineAttachments = $inlineAttachments;
        return $this;
    }

    public function getPrio(): int
    {
        return $this->prio;
    }

    public function setPrio(int $prio): self
    {
        $this->prio = $prio;
        return $this;
    }

    public function getCampaign(): string
    {
        return $this->campaign;
    }

    public function setCampaign(string $campaign): self
    {
        $this->campaign = $campaign;
        return $this;
    }

    public function getDeduplicateCampaign(): int
    {
        return $this->deduplicateCampaign;
    }

    public function setDeduplicateCampaign(int $deduplicateCampaign): self
    {
        $this->deduplicateCampaign = $deduplicateCampaign;
        return $this;
    }

    public function getTrackOpen(): int
    {
        return $this->trackOpen;
    }

    public function setTrackOpen(int $trackOpen): self
    {
        $this->trackOpen = $trackOpen;
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

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    public function getVars(): array
    {
        return $this->vars;
    }

    public function setVars(array $vars): self
    {
        $this->vars = $vars;
        return $this;
    }
}
