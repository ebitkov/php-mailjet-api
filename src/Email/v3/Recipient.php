<?php

namespace ebitkov\Mailjet\Email\v3;

final class Recipient extends \ebitkov\Mailjet\Email\Contact
{
    /**
     * @param array<string, string> $vars
     */
    public function __construct(
        string $email,
        ?string $name = null,
        private array $vars = []
    ) {
        parent::__construct($email, $name);
    }

    public function getVars(): ?string
    {
        if ($this->vars) {
            return json_encode($this->vars);
        }
        return null;
    }

    public function setVars(string $vars): void
    {
        $this->vars = json_decode($vars, true);
    }
}
