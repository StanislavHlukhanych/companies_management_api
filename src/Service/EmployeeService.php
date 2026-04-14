<?php

namespace App\Service;

use App\Dto\EmployeeDto;
use App\Entity\Employee;
use App\Repository\CompanyRepository;
use App\Repository\EmployeeRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EmployeeService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CompanyRepository $companyRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly EmployeeRepository $employeeRepository,
    ){}

    public function create(EmployeeDto $employeeDto): Employee
    {
        $company = $this->companyRepository->find($employeeDto->companyId);
        if (!$company) {
            throw new NotFoundHttpException('Company with the provided ID does not exist');
        }

        if ($this->employeeRepository->findOneBy(['email' => $employeeDto->email])) {
            throw new UnprocessableEntityHttpException('Email already exists');
        }

        $employee = new Employee();
        $employee->setFirstName($employeeDto->firstName);
        $employee->setLastName($employeeDto->lastName);
        $employee->setEmail($employeeDto->email);
        $employee->setCompany($company);

        foreach ($employeeDto->projectIds as $projectId) {
            $project = $this->projectRepository->find($projectId);
            if ($project) {
                $employee->addProject($project);
            }
        }

        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        return $employee;
    }

    public function update(Employee $employee, EmployeeDto $employeeDto): Employee
    {
        $company = $this->companyRepository->find($employeeDto->companyId);
        if (!$company) {
            throw new NotFoundHttpException('Company with the provided ID does not exist');
        }

        if ($employee->getEmail() !== $employeeDto->email &&
            $this->employeeRepository->findOneBy(['email' => $employeeDto->email])) {
            throw new UnprocessableEntityHttpException('Email already exists');
        }

        $employee->setFirstName($employeeDto->firstName);
        $employee->setLastName($employeeDto->lastName);
        $employee->setEmail($employeeDto->email);
        $employee->setCompany($company);

//        foreach ($employee->getProjects() as $project) {
//            $employee->removeProject($project);
//        }

        $employee->getProjects()->clear();

        $newProjects = $this->projectRepository->findBy(['id' => $employeeDto->projectIds]);

        foreach ($newProjects as $newProject) {
            $employee->addProject($newProject);
        }

//        foreach ($employeeDto->projectIds as $projectId) {
//            $project = $this->projectRepository->find($projectId);
//            if ($project) {
//                $employee->addProject($project);
//            }
//        }

        $this->entityManager->flush();

        return $employee;
    }

    public function delete(Employee $employee): void
    {
        $this->entityManager->remove($employee);
        $this->entityManager->flush();
    }
}
