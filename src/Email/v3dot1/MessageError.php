<?php

namespace ebitkov\Mailjet\Email\v3dot1;

class MessageError
{
    public string $errorIdentifier;

    public string $errorCode;

    public int $statusCode;

    public string $errorMessage;

    # todo ErrorRelatedTo
}
