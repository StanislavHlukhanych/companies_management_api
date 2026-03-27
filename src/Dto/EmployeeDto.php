<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class EmployeeDto
{
    #[Assert\NotBlank(message: 'First name is required.')]
    public string $firstName;
    #[Assert\NotBlank(message: 'Last name is required.')]
    public string $lastName;
    #[Assert\NotBlank(message: 'Email is required.')]
    #[Assert\Email(message: 'The email "{{ value }}" is not a valid email.')]
    public string $email;
    #[Assert\NotBlank(message: 'Company ID is required.')]
    public int $companyId;

    /**
     * @var int[]
     */
    #[Assert\All([
        new Assert\Type('integer'),
        new Assert\Positive()
    ])]
    public array $projectIds = [];
}
