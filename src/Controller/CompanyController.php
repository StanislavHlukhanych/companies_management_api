<?php

namespace App\Controller;

use App\Dto\CompanyDto;
use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Service\CompanyService;
use App\Trait\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class CompanyController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly CompanyService $CompanyService
    ){}

    #[Route('/api/companies', name: 'app_company', methods: ['GET'])]
    public function index(CompanyRepository $companyRepository): JsonResponse
    {
        $companies = $companyRepository->findAll();

        return $this->success($companies, Response::HTTP_OK, ['company:read']);
    }

    #[Route('/api/companies/{id}', name: 'app_company_show', methods: ['GET'])]
    public function show(Company $company): JsonResponse
    {
        return $this->success($company, Response::HTTP_OK, ['company:read']);
    }

    #[Route('/api/companies', name: 'app_company_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CompanyDto $companyDto,): JsonResponse
    {
        $company = $this->CompanyService->create($companyDto);

        return $this->success($company, Response::HTTP_CREATED, ['company:read']);
    }

    #[Route('/api/companies/{id}', name: 'app_company_update', methods: ['PUT'])]
    public function update(Company $company, #[MapRequestPayload] CompanyDto $companyDto): JsonResponse
    {
        $company = $this->CompanyService->update($company, $companyDto);

        return $this->success($company, Response::HTTP_OK, ['company:read']);
    }

    #[Route('/api/companies/{id}', name: 'app_company_delete', methods: ['DELETE'])]
    public function delete(Company $company): JsonResponse
    {
        $this->CompanyService->delete($company);

        return $this->success(null, Response::HTTP_OK);
    }
}
