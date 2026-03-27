<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CompanyDto
{
    #[Assert\NotBlank(message: 'Company name is required.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Company name must be at least {{ limit }} characters long.',
        maxMessage: 'Company name cannot be longer than {{ limit }} characters.'
    )]
    public string $name;
    public ?string $description = null;
}
