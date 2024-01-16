<?php

namespace ebitkov\Mailjet;

use Exception;
use Mailjet\Response;
use Throwable;

class RequestAborted extends Exception
{
    public function __construct(int $tries, ?Response $response, ?Throwable $previous = null)
    {
        if ($response) {
            $previous = new RequestFailed($response, $previous);
        }

        parent::__construct(
            sprintf('Request aborted after %d tries.', $tries),
            500,
            $previous
        );
    }
}
