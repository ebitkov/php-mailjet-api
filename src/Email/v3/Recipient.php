<?php

namespace ebitkov\Mailjet\Email\v3;

use ebitkov\Mailjet\Email\EmailAddress;

class Recipient extends EmailAddress
{
    /**
     * @var array<string, string>
     */
    private ?array $variables;

    public function getVariables(): string
    {
        return json_encode($this->variables);
    }

    /**
     * @param array<string, string> $variables
     */
    public function setVariables(array $variables): self
    {
        $this->variables = $variables;
        return $this;
    }

    public function addVariable(string $name, string|int|float $value): self
    {
        $this->variables[$name] = (string)$value;
        return $this;
    }
}
