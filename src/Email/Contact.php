<?php

namespace ebitkov\Mailjet\Email;

use Symfony\Component\Validator\Constraints as Assert;

class Contact
{
    public function __construct(
        #[Assert\Email]
        public string $email,
        public ?string $name = null
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
}
