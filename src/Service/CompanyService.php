<?php

namespace App\Service;

use App\Dto\CompanyDto;
use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CompanyService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ){}

    public function create(CompanyDto $companyDto): Company
    {
        $company = new Company();
        $company->setName($companyDto->name);
        $company->setDescription($companyDto->description);

        $this->entityManager->persist($company);
        $this->entityManager->flush();

        return $company;
    }

    public function update(Company $company, CompanyDto $companyDto): Company
    {
        $company->setName($companyDto->name);
        $company->setDescription($companyDto->description);

        $this->entityManager->flush();

        return $company;
    }

    public function delete(Company $company): void
    {
        if (count($company->getEmployees()) > 0 || count($company->getProjects()) > 0) {
            throw new BadRequestHttpException('Cannot delete a company that has employees or projects');
        }

        $this->entityManager->remove($company);
        $this->entityManager->flush();
    }
}
