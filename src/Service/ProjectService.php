<?php

namespace App\Service;

use App\Dto\ProjectDto;
use App\Entity\Project;
use App\Repository\CompanyRepository;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProjectService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CompanyRepository $companyRepository,
        private readonly EmployeeRepository $employeeRepository,
    ){}

    public function create(ProjectDto $projectDto): Project
    {
        $company = $this->companyRepository->find($projectDto->companyId);
        if (!$company) {
            throw new NotFoundHttpException('Company with the provided ID does not exist');
        }

        $project = new Project();
        $project->setTitle($projectDto->title);
        $project->setDescription($projectDto->description);
        $project->setCompany($company);

        foreach ($projectDto->participantIds as $participant) {
            $employee = $this->employeeRepository->find($participant);
            if ($employee) {
                $project->addParticipant($employee);
            }
        }

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return $project;
    }

    public function update(Project $project, ProjectDto $projectDto): Project
    {
        $company = $this->companyRepository->find($projectDto->companyId);
        if (!$company) {
            throw new NotFoundHttpException('Company with the provided ID does not exist');
        }

        $project->setTitle($projectDto->title);
        $project->setDescription($projectDto->description);
        $project->setCompany($company);

        foreach ($project->getParticipants() as $participant) {
            $project->removeParticipant($participant);
        }

        foreach ($projectDto->participantIds as $participant) {
            $employee = $this->employeeRepository->find($participant);
            if ($employee) {
                $project->addParticipant($employee);
            }
        }

        $this->entityManager->flush();

        return $project;
    }

    public function delete(Project $project): void
    {
        $this->entityManager->remove($project);
        $this->entityManager->flush();
    }
}
