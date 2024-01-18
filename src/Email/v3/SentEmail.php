<?php

namespace ebitkov\Mailjet\Email\v3;

use ebitkov\Mailjet\Email\Resource;

/**
 * Represents a send email.
 */
final class SentEmail implements Resource
{
    public string $email;
    public int $messageId;
    public string $messageUuid;
}
