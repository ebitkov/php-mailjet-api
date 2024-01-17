<?php

namespace ebitkov\Mailjet\Email;

final class Recipient
{
    /**
     * @param array<string, string> $vars
     */
    public function __construct(
        public string $email,
        public ?string $name = null,
        private array $vars = []
    ) {
    }

    public function __toString(): string
    {
        return sprintf(
            '%s<%s>',
            $this->name ? '"' . $this->name . '" ' : '',
            $this->email
        );
    }

    public function setVars(string $vars): void
    {
        $this->vars = json_decode($vars, true);
    }

    public function getVars(): ?string
    {
        if ($this->vars) {
            return json_encode($this->vars);
        }
        return null;
    }
}
