<?php

namespace ebitkov\Mailjet\Email\v3;

use ebitkov\Mailjet\Email\Recipient;

/**
 * An email message for the Send API v3.
 */
final class Email
{
    public string $fromEmail;

    public string $fromName;

    public bool $sender;

    /**
     * @var list<Recipient>
     */
    public array $recipients;

    public string $subject;

    public string $textPart;

    public string $htmlPart;

    public int $templateId;

    public bool $templateLanguage;

    public string $templateErrorReporting;

    /**
     * @var "deliver"|"0"
     */
    public string $templateErrorDeliver;

    /**
     * @var list<Attachment>
     */
    public array $attachments;

    /**
     * @var list<Attachment>
     */
    public array $inlineAttachments;

    public int $prio;

    public string $campaign;

    /**
     * @var int<0, 1>
     */
    public int $deduplicateCampaign;

    /**
     * @var int<0, 2>
     */
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
    private array $cc = [];

    /**
     * @var list<Recipient>
     */
    private array $bcc = [];

    public function addRecipient(Recipient $recipient): void
    {
        $this->recipients[] = $recipient;
    }

    public function addTo(Recipient $recipient): void
    {
        $this->to[] = $recipient;
    }

    public function addCc(Recipient $recipient): void
    {
        $this->cc[] = $recipient;
    }

    public function addBcc(Recipient $recipient): void
    {
        $this->bcc[] = $recipient;
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
}
