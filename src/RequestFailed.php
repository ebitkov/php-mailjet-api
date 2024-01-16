<?php

namespace ebitkov\Mailjet;

use Exception;
use Mailjet\Request;
use Mailjet\Response;
use Throwable;

class RequestFailed extends Exception
{
    public Request $request;


    public function __construct(Response $response, ?Throwable $previous = null)
    {
        $this->request = $response->getRequest();

        parent::__construct(
            $response->getReasonPhrase(),
            $response->getStatus(),
            $previous
        );
    }
}