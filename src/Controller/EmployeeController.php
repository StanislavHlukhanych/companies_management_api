<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Repository\CompanyRepository;
use App\Repository\EmployeeRepository;
use App\Repository\ProjectRepository;
use App\Trait\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class EmployeeController extends AbstractController
{
    use ApiResponseTrait;

    #[Route('/api/employees', name: 'app_employee', methods: ['GET'])]
    public function index(EmployeeRepository $employeeRepository): JsonResponse
    {
        $employees = $employeeRepository->findAll();

        return $this->success($employees, Response::HTTP_OK, ['employee:read']);
    }

    #[Route('/api/employees/{id}', name: 'app_employee_show', methods: ['GET'])]
    public function show(Employee $employee): JsonResponse
    {
        return $this->success($employee, Response::HTTP_OK, ['employee:read']);
    }

    #[Route('/api/employees', name: 'app_employee_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        CompanyRepository $companyRepository,
        ProjectRepository $projectRepository,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $data = $request->toArray();

        $companyId = $data['companyId'] ?? null;
        if (!$companyId) {
            return $this->fail([
                'companyId' => 'This field is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $company = $companyRepository->find($companyId);
        if (!$company) {
            return $this->fail([
                'companyId' => 'Company with the provided ID does not exist'
            ], Response::HTTP_NOT_FOUND);
        }

        $employee = $serializer->deserialize(
            $request->getContent(),
            Employee::class,
            'json',
            ['groups' => 'employee:write']
        );

        $errors = $validator->validate($employee);

        if (count($errors) > 0) {
            $dataErrors = [];
            foreach ($errors as $error) {
                $dataErrors[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->fail($dataErrors, 422);
        }

        if (isset($data['projectIds']) && is_array($data['projectIds'])) {
            foreach ($data['projectIds'] as $projectId) {
                $project = $projectRepository->find($projectId);
                if (!$project) {
                    return $this->fail([
                        'projectIds' => "Project with ID $projectId not found"
                    ], Response::HTTP_NOT_FOUND);
                }
                $employee->addProject($project);
            }
        }

        $employee->setCompany($company);

        $entityManager->persist($employee);
        $entityManager->flush();

        return $this->success($employee, Response::HTTP_CREATED, ['employee:read']);
    }

    #[Route('/api/employees/{id}', name: 'app_employee_update', methods: ['PUT'])]
    public function update(
        Request $request,
        CompanyRepository $companyRepository,
        ProjectRepository $projectRepository,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        Employee $employee
    ): JsonResponse
    {
        $data = $request->toArray();

        $serializer->deserialize(
            $request->getContent(),
            Employee::class,
            'json',
            ['object_to_populate' => $employee, 'groups' => 'employee:write']
        );

        if (isset($data['companyId'])) {
            $company = $companyRepository->find($data['companyId']);
            if (!$company) {
                return $this->fail(
                    ['companyId' => 'Company with the provided ID does not exist'],
                    Response::HTTP_NOT_FOUND);
            }
            $employee->setCompany($company);
        }

        if (isset($data['projectIds']) && is_array($data['projectIds'])) {
            foreach ($employee->getProjects()->toArray() as $project) {
                $employee->removeProject($project);
            }

            foreach ($data['projectIds'] as $projectId) {
                $project = $projectRepository->find($projectId);
                if (!$project) {
                    return $this->fail([
                        'projectIds' => "Project with ID $projectId not found"
                    ], Response::HTTP_NOT_FOUND);
                }
                $employee->addProject($project);
            }
        }

        $entityManager->flush();

        return $this->success($employee, Response::HTTP_OK, ['employee:read']);
    }

    #[Route('/api/employees/{id}', name: 'app_employee_delete', methods: ['DELETE'])]
    public function delete(
        EntityManagerInterface $entityManager,
        Employee $employee
    ): JsonResponse
    {
        $entityManager->remove($employee);
        $entityManager->flush();

        return $this->success(null, Response::HTTP_OK);
    }
}
