<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Repository\CompanyRepository;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class EmployeeController extends AbstractController
{
    #[Route('/api/employees', name: 'app_employee', methods: ['GET'])]
    public function index(EmployeeRepository $employeeRepository): JsonResponse
    {
        $employees = $employeeRepository->findAll();

        return $this->json($employees, Response::HTTP_OK, [], ['groups' => 'employee:read']);
    }

    #[Route('/api/employees/{id}', name: 'app_employee_show', methods: ['GET'])]
    public function show(EmployeeRepository $employeeRepository, int $id): JsonResponse
    {
        $employee = $employeeRepository->find($id);
        if(!$employee) {
            return $this->json(['error' => 'Employee not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($employee, Response::HTTP_OK, [], ['groups' => 'employee:read']);
    }

    #[Route('/api/employees', name: 'app_employee_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager,
                           SerializerInterface $serializer, CompanyRepository $companyRepository): JsonResponse
    {
        $data = $request->toArray();
        $employee = $serializer->deserialize(json_encode($data), Employee::class, 'json', ['groups' => 'employee:write']);

        $companyId = $data['companyId'] ?? null;

        if (!$companyId) {
            return $this->json(['error' => 'Company ID is required'], Response::HTTP_BAD_REQUEST);
        }

        $company = $companyRepository->find($companyId);
        if (!$company) {
            return $this->json(['error' => 'Company not found'], Response::HTTP_NOT_FOUND);
        }

        $employee->setCompany($company);

        $entityManager->persist($employee);
        $entityManager->flush();

        return $this->json(['message' => 'Employee created successfully'], Response::HTTP_CREATED);
    }

    #[Route('/api/employees/{id}', name: 'app_employee_update', methods: ['PUT'])]
    public function update(Request $request, EmployeeRepository $employeeRepository, CompanyRepository $companyRepository,
                           EntityManagerInterface $entityManager, SerializerInterface $serializer, int $id): JsonResponse
    {
        $employee = $employeeRepository->find($id);
        if (!$employee) {
            return $this->json(['error' => 'Employee not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $request->toArray();
        $serializer->deserialize(json_encode($data), Employee::class, 'json',
            ['object_to_populate' => $employee, 'groups' => 'employee:write']);

        if (isset($data['companyId'])) {
            $company = $companyRepository->find($data['companyId']);
            if (!$company) {
                return new JsonResponse(['error' => 'Company not found'], Response::HTTP_NOT_FOUND);
            }
            $employee->setCompany($company);
        }

        $entityManager->flush();

        return $this->json(['message' => 'Employee updated successfully'], Response::HTTP_OK);
    }

    #[Route('/api/employees/{id}', name: 'app_employee_delete', methods: ['DELETE'])]
    public function delete(EmployeeRepository $employeeRepository,
                           EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $employee = $employeeRepository->find($id);
        if (!$employee) {
            return $this->json(['error' => 'Employee not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($employee);
        $entityManager->flush();

        return $this->json(['message' => 'Employee deleted successfully'], Response::HTTP_OK);
    }
}
