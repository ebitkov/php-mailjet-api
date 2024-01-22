<?php

namespace ebitkov\Mailjet\Email\v3dot1;

use ebitkov\Mailjet\Email\Resource;
use Symfony\Component\Serializer\Attribute\SerializedName;

class SentMessageList implements Resource
{
    public string $status;

    /**
     * @var MessageError[]
     */
    public array $errors;

    public string $customId;

    /**
     * @var SentMessage[]
     */
    public array $to;

    /**
     * @var SentMessage[]
     */
    public array $cc;

    /**
     * @var SentMessage[]
     */
    public array $bcc;
}
