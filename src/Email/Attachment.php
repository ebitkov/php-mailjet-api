<?php

namespace ebitkov\Mailjet\Email;

use Symfony\Component\Validator\Constraints as Assert;

class Attachment
{
    #[Assert\NotBlank]
    public string $filename;

    #[Assert\NotBlank]
    public string $contentType;

    #[Assert\NotBlank]
    public string $base64Content;
}
