<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ProjectDto
{
    #[Assert\NotBlank(message: 'Project title is required.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Project title must be at least {{ limit }} characters long.',
        maxMessage: 'Project title cannot be longer than {{ limit }} characters.'
    )]
    public string $title;
    public ?string $description = null;
    #[Assert\NotBlank(message: 'Company ID is required.')]
    public int $companyId;

    /**
    * @var int[]
    */
    #[Assert\All([
        new Assert\Type('integer'),
        new Assert\Positive()
    ])]
    public array $participantIds = [];
}
