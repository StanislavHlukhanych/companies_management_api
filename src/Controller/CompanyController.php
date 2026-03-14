<?php

namespace App\Controller;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class CompanyController extends AbstractController
{
    #[Route('/api/companies', name: 'app_company', methods: ['GET'])]
    public function index(CompanyRepository $companyRepository): JsonResponse
    {
        $companies = $companyRepository->findAll();

        return $this->json($companies, Response::HTTP_OK, [], ['groups' => 'company:read']);
    }

    #[Route('/api/companies/{id}', name: 'app_company_show', methods: ['GET'])]
    public function show(CompanyRepository $companyRepository, int $id): JsonResponse
    {
        $company = $companyRepository->find($id);

        return $this->json($company, Response::HTTP_OK, [], ['groups' => 'company:read']);
    }

    #[Route('/api/companies', name: 'app_company_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager,
                           SerializerInterface $serializer): JsonResponse
    {
        $data = $request->getContent();
        $company = $serializer->deserialize($data, Company::class, 'json');

        $entityManager->persist($company);
        $entityManager->flush();

        return $this->json(['message' => 'Company created successfully'], Response::HTTP_CREATED);
    }

    #[Route('/api/companies/{id}', name: 'app_company_update', methods: ['PUT'])]
    public function update(Request $request, CompanyRepository $companyRepository, EntityManagerInterface $entityManager,
                           SerializerInterface $serializer, int $id): JsonResponse
    {
        $company = $companyRepository->find($id);
        if (!$company) {
            return new JsonResponse(['error' => 'Company not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->getContent();
        $serializer->deserialize($data, Company::class, 'json', ['object_to_populate' => $company]);

        $entityManager->flush();

        return $this->json(['message' => 'Company updated successfully'], Response::HTTP_OK);
    }

    #[Route('/api/companies/{id}', name: 'app_company_delete', methods: ['DELETE'])]
    public function delete(CompanyRepository $companyRepository, EntityManagerInterface $entityManager,
                           int $id): JsonResponse
        {
            $company = $companyRepository->find($id);
            if (!$company) {
                return new JsonResponse(['error' => 'Company not found'], Response::HTTP_NOT_FOUND);
            }

            $entityManager->remove($company);
            $entityManager->flush();

            return $this->json(['message' => 'Company deleted successfully'], Response::HTTP_OK);
        }
}
