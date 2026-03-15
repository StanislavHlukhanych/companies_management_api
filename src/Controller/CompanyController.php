<?php

namespace App\Controller;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Trait\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class CompanyController extends AbstractController
{
    use ApiResponseTrait;

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
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ): JsonResponse
    {
        $data = $request->getContent();
        $company = $serializer->deserialize($data, Company::class, 'json', ['groups' => 'company:write']);

        $entityManager->persist($company);
        $entityManager->flush();

        return $this->success($company, Response::HTTP_CREATED, ['company:read']);
    }

    #[Route('/api/companies/{id}', name: 'app_company_update', methods: ['PUT'])]
    public function update(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        Company $company
    ): JsonResponse
    {
        $data = $request->getContent();
        $serializer->deserialize($data, Company::class, 'json',
            ['object_to_populate' => $company, 'groups' => 'company:write']);

        $entityManager->flush();

        return $this->success($company, Response::HTTP_OK, ['company:read']);
    }

    #[Route('/api/companies/{id}', name: 'app_company_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, Company $company): JsonResponse
    {
        if (count($company->getEmployees()) > 0 || count($company->getProjects()) > 0) {
            return $this->fail([
                'error' => 'Cannot delete a company that has employees or projects'
            ], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->remove($company);
        $entityManager->flush();

        return $this->success(null, Response::HTTP_OK);
    }
}
