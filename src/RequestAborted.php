<?php

namespace ebitkov\Mailjet;

use Mailjet\Response;
use Throwable;

class RequestAborted extends RequestFailed
{
    public function __construct(int $tries, Response $response, ?Throwable $previous = null)
    {
        parent::__construct($response, $previous);
        $this->message = sprintf('Request aborted after %d tries.', $tries);
    }
}