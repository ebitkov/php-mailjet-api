<?php

namespace ebitkov\Mailjet\Email\v3dot1;

class SentMessage
{
    public string $email;
    public string $messageUuid;
    public int|string $messageId;
    public string $messageHref;
}
