<?php

namespace ebitkov\Mailjet\Email\v3dot1;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * An email for Send API v3.1.
 * @see https://dev.mailjet.com/email/reference/send-emails#v3_1_post_send
 */
final class EmailList
{
    public bool $sandboxMode;

    public bool $advanceErrorHandling;

    public Message $globals;

    /**
     * @var Message[]
     */
    #[Assert\NotBlank]
    #[Assert\All([
        new Assert\Type(Message::class)
    ])]
    public array $messages;


    public function addMessage(Message $message): self
    {
        $this->messages[] = $message;
        return $this;
    }
}
