<?php

namespace ebitkov\Mailjet\Email\v3;

use ebitkov\Mailjet\Email\Recipient;
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

    public function addAttachment(Attachment $attachment): void
    {
        $this->attachments[] = $attachment;
    }

    public function addInlineAttachment(Attachment $attachment): void
    {
        $this->inlineAttachments[] = $attachment;
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

    public function addRecipient(Recipient $recipient): void
    {
        $this->recipients[] = $recipient;
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
}
